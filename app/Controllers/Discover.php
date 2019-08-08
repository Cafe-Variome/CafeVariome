<?php namespace App\Controllers;

/**
 * Discover.php
 * Created: 16/07/2019
 * 
 * @author Mehdi Mehtarizadeh
 * 
 *
 */

use App\Models\UIData;
use App\Models\Settings;
use App\Models\Source;
use App\Libraries\KeyCloak;
use App\Libraries\CafeVariome;
use App\Libraries\CafeVariome\Query;
use App\Helpers\AuthHelper;
use CodeIgniter\Config\Services;
use Elasticsearch;

class Discover extends CVUI_Controller{

    private $authAdapter;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->session = Services::session();
		$this->db = \Config\Database::connect();
        $this->setting =  Settings::getInstance($this->db);

        $this->authAdapter = new KeyCloak();
		$this->authAdapter->setSession($this->session);
    }

    public function query_builder($network_key){
        
        $uidata = new UIData();

        if ($network_key) {
            $this->session->set(array('network_key' => $network_key));
        } else {
            redirect('discover/proceed_to_query/query_builder', 'refresh');
        }

        // Check if the user is in the master network group for this network
        
        $user_id = $this->authAdapter->getUserId();
        $is_user_member_of_master_network_group_for_network = true;//$this->network_model->isUserMemberOfMasterNetworkGroupForNetwork($user_id, $network_key);
        if (!$is_user_member_of_master_network_group_for_network) {
            show_error("You are not a member of the master group for this network so cannot access any discovery interfaces. In order to search any networks you need to get an administrator to add you to the master network group for each network.");
        }
        
        $uidata->data['network_key'] = $network_key;
        
        error_log("User: " . $this->session->get('email') . " has chosen network: $network_key || " . date("Y-m-d H:i:s"));

        $token = $this->session->get('Token');
        $installation_urls = json_decode(AuthHelper::authPostRequest(array('installation_key' => $this->setting->settingData['installation_key'], 'network_key' => $network_key), $this->setting->settingData['auth_server'] . "network/get_all_installation_ips_for_network"), true);
        $data = AuthHelper::authPostRequest(array('installation_key' => $this->setting->settingData['installation_key']), $this->setting->settingData['auth_server'] . "network/get_all_installations_for_network");

        $data = stripslashes($data);
        $data = json_decode($data, 1);
        if (array_key_exists('error', $data)) show_error($data['error']);
        // Set the federated installs in the session so they can be used by variantcount
        $this->session->set(array('federated_installs' => $data['installation_urls']));
        $this->session->set(array('network_threshold' => $data['network_threshold']));

        if (!$this->checkElasticSearch()) {
            show_error("The query builder interface is currently not accessible as Elasticsearch is not running. Please get an administrator to start Elasticsearch and then try again.");
        }

        $uidata->title = "Discover - Query Builder";
        $uidata->css = array(CSS.'jquery.querybuilder.css');  

        $basic = $this->session->get('query_builder_basic') == "yes" ? 1 : 0;
        $advanced = $this->session->get('query_builder_advanced') == "yes" ? 1 : 0;
        $precan = $this->session->get('query_builder_precan') == "yes" ? 1 : 0;
        $this->data['create_precan_query'] = $this->session->get('create_precan_query');
        
        if($basic && !$advanced && !$precan)  {
            if(PHENOTYPE_CATEGORIES) {
                $uidata->javascript = array(JS.'bootstrap-notify.js', JS.'mustache.min.js', JS.'query_builder_config.js', JS.'query_builder_category.js');
                $data = $this->wrapData($uidata);
                return view("discover/query_builder/main", $data);
            } else {
                $this->javascript = array(JS.'bootstrap-notify.js',JS.'mustache.min.js', JS.'query_builder_config.js', JS.'query_builder.js');
                $data = $this->wrapData($uidata);
                return view("discover/query_builder/main", $data);
            }
        } else {
                $json = json_decode(file_get_contents(base_url() . "resources/precanned.json"), 1);
                if($json) {

                    $this->data['precan_active'] = [];
                    $this->data['precan_inactive'] = [];

                    foreach ($json as $key => $value) {
                        if(isset($value['network_key']) && $value['network_key'] == $network_key) {
                            if(!isset($uidata->data['precanned_queries']))
                                $uidata->data['precanned_queries'][] = $value['source'];
                            else {
                                if(!in_array($value['source'], $this->data['precanned_queries']))
                                    $uidata->data['precanned_queries'][] = $value['source'];
                            }

                            if($value['status'] == 1)
                                $uidata->data['precan_active'][] = array('api' => htmlspecialchars(json_encode($value)), 'queryString' => $value['queryString'], 'user_email' => $value['user_email'], 'date_time' => $value['date_time'], 'notes' => $value['notes'], 'source' => $value['source'], 'case_control' => $value['case_control']);
                            elseif($value['status'] == -1)
                                $uidata->data['precan_inactive'][] = array('api' => htmlspecialchars(json_encode($value)), 'queryString' => $value['queryString'], 'user_email' => $value['user_email'], 'date_time' => $value['date_time'], 'notes' => $value['notes'], 'source' => $value['source'], 'case_control' => $value['case_control']);
                        }
                    }
                }

                if(PHENOTYPE_CATEGORIES) {
                    $uidata->javascript = array(JS.'mustache.min.js', JS.'query_builder_config.js', JS.'query_builder_precan_v2_category.js', JS.'query_builder_advanced_v2_category.js');
                } else {
                    $uidata->data['precanned_queries'] = json_decode(file_get_contents(base_url() . "resources/precanned.json"), 1);
                 $uidata->javascript = array(JS.'mustache.min.js', JS.'query_builder_config.js', JS.'query_builder_precan.js', JS.'query_builder_advanced.js');
                }

                $uidata->data['qb_basic'] = $basic ? 1 : 0;
                $uidata->data['qb_advanced'] = $advanced ? 1 : 0;
                $uidata->data['qb_precan'] = $precan ? 1 : 0;
                error_log("here");

                $data = $this->wrapData($uidata);
                return view("query_builder/main_precan_v2", $data);

        }
        
        $data = AuthHelper::authPostRequest(array('installation_key' => $this->setting->settingData['installation_key']), $this->setting->settingData['auth_server'] . "network/get_all_installations_for_networks_this_installation_is_a_member_of");
        $federated_installs = json_decode(stripslashes($data), 1);

        error_log("federated_installs -> " . print_r($federated_installs, 1));
    }


    function executeQuery($network = ''){
        $uidata = new UIData();
        $time_in = new \DateTime();
        error_log("Query time in: " . date("Y-m-d H:i:sa"));
        $view_derids = $this->session->get('view_derids');

        $is_precan =  $this->request->getVar('precan') ? true : false;
        $query =  $this->request->getVar('jsonAPI');

        $network_to_search = $query['network_to_search'];
        
        $uidata->data['network_key'] = $network_to_search;

        $parameters = array('syntax' => 'elasticsearch');

        $cafeVariomeQuery = new Query($parameters);

        $query_statement = $cafeVariomeQuery->parse($query);
        $term = $query_statement[0];

        error_log("User: " . $this->session->get('email') . " query statement: " . $query_statement[0] . " || " . date("Y-m-d H:i:s"));
        if (preg_match("/\//", $term)) {
            $pos = strpos($term, "/");
            $term = substr_replace($term, "\\", $pos, 0);
        }
        if ($term) {
            $uidata->data['term'] = $term;
  
        } else {
            show_error("You must specify a search term");
        }
        
        $sourceModel =  new Source($this->db);
        $sources = array();
        $sources = $sourceModel->getOnlineSources();


        // Check if Base url is set from precan query else
        // Get the federated installs to search from session (set when the discovery interface first loads)
        $federated_installs_array = isset($query['base_url']) ? array(array('network_key' => $network, 'installation_base_url' => $query['base_url'])) : $this->session->get('federated_installs');
        // If there's some federated installs to search then go through each one and get the variant counts
        if (!empty($federated_installs_array)) {
            if (!array_key_exists('error', $federated_installs_array)) {
                $c = 0;

                $network_threshold = $this->session->get('network_threshold');
                $access_token = $this->authAdapter->getToken();
                foreach ($federated_installs_array as $install) {
                    $c++;
                    $network_key = $install['network_key'];
                    $install_uri = $install['installation_base_url'];
                    if($install_uri == "http://www164.lamp.le.ac.uk/prepadcentral/") continue;
                    if($install_uri == "https://www237.lamp.le.ac.uk/EpadTwo") continue;

                        $install_uri = rtrim($install_uri, "/");

                    $postdata = http_build_query(
                        array(
                            'term' => $term,
                            'access_token' => $access_token,
                            'network_key' => $network_key,
                            'network_threshold' => '0'
                        )
                    );

                    $opts = array('http' =>
                        array(
                            'method' => 'POST',
                            'header'  => 'Content-type: application/x-www-form-urlencoded',
                            'content' => $postdata,
                            'timeout' => 10
                        )
                    );
                    $context = stream_context_create($opts);
                    $url = "https://www185.lamp.le.ac.uk/EpadGreg/discover_federated/variantcount4/". (isset($query['base_url']) ? "/" . urlencode($query['source']) : "");

                    $time1 = new \DateTime();
                    
                    $all_counts_json = file_get_contents($url, false, $context);
                    $time2 = new \DateTime();
                    $interval = $time2->diff($time1);
                    error_log("url: $install_uri || time: " . $interval->format('%im:%ss') . " || counts -> $all_counts_json");
                    $all_counts = json_decode($all_counts_json, 1);
                    var_dump($all_counts_json);
                    $federated_site_title = $all_counts['site_title'];
                    unset($all_counts['site_title']);
                    if (!empty($all_counts)) {
                        foreach ($all_counts as $federated_source => $counts_for_source) {
                            $federated_source_name = $federated_source . "__install_$c";
                            $sources[$federated_source_name] = "$federated_source ($federated_site_title)";
                            $uidata->data['counts'][$federated_source_name] = $counts_for_source;
                            if (is_numeric($counts_for_source['restrictedAccess']) && is_numeric($counts_for_source['openAccess'])) {
                                $counts_for_log = (isset($counts_for_source['restrictedAccess']) ? $counts_for_source['restrictedAccess'] : 0) + (isset($counts_for_source['openAccess']) ? $counts_for_source['openAccess'] : 0);
                            }

                            $uidata->data['install_uri'][$federated_source_name] = $install_uri;
                            $uidata->data['source_types'][$federated_source_name] = "federated";
                        }
                    }
                }
            }
        }

        $uidata->data['view_derids'] = $view_derids;
        $uidata->data['sources_full'] = $sources;

        $time_out = new DateTime();
        error_log("Query time out: " . date("Y-m-d H:i:s"));
        $interval = $time_out->diff($time_in);
        error_log($interval->format('%im:%ss'));

        $data = $this->wrapData($uidata);
        return view('pages/sources_table', $data); // Don't use _render as headers are already sent, html output from the view is sent back to ajax function and appended to div       
    }



    function checkElasticSearch() {
        $hosts = (array)$this->setting->settingData['elastic_url'];
        $client = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
    
        try {
            $indices = $client->cat()->indices(array('index' => '*'));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}