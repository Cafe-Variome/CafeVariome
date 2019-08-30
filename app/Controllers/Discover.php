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

    /**
	 * Constructor
	 *
	 */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger){
        parent::setProtected(true);
        parent::setIsAdmin(true);
        parent::initController($request, $response, $logger);

		$this->validation = Services::validation();
        $this->networkModel = new \App\Models\Network($this->db);
        $this->userModel = new \App\Models\User($this->db);
    }

    public function query_builder($network_key){
        
        $uidata = new UIData();

        if ($network_key) {
            $this->session->set(array('network_key' => $network_key));
        } 
        else {
            return redirect()->to(base_url('discover/proceed_to_query/query_builder'));
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
        $uidata->css = array(VENDOR.'vakata/jstree/dist/themes/default/style.css', CSS.'jquery.querybuilder.css');  
        $basic = $this->session->get('query_builder_basic') == "yes" ? 1 : 0;
        $advanced = $this->session->get('query_builder_advanced') == "yes" ? 1 : 0;
        $precan = $this->session->get('query_builder_precan') == "yes" ? 1 : 0;
        $this->data['create_precan_query'] = $this->session->get('create_precan_query');
        
        if($basic && !$advanced && !$precan)  {
            if(PHENOTYPE_CATEGORIES) {
                $uidata->javascript = array(VENDOR.'vakata/jstree/dist/jstree.js', VENDOR.'components/jqueryui/jquery-ui.js',JS.'bootstrap-notify.js', JS.'mustache.min.js', JS.'query_builder_config.js', JS.'query_builder_category.js');
                $data = $this->wrapData($uidata);
                return view("discover/query_builder", $data);
            } else {
                $this->javascript = array(VENDOR.'vakata/jstree/dist/jstree.js', VENDOR.'components/jqueryui/jquery-ui.js', JS.'bootstrap-notify.js',JS.'mustache.min.js', JS.'query_builder_config.js', JS.'query_builder.js');
                $data = $this->wrapData($uidata);
                return view("discover/query_builder", $data);
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
                    $uidata->javascript = array(VENDOR.'vakata/jstree/dist/jstree.js', JS.'mustache.min.js', JS.'query_builder_config.js', JS.'query_builder_precan_v2_category.js', JS.'query_builder_advanced_v2_category.js');
                } else {
                    $uidata->data['precanned_queries'] = json_decode(file_get_contents(base_url() . "resources/precanned.json"), 1);
                    $uidata->javascript = array(VENDOR.'vakata/jstree/dist/jstree.js', JS.'mustache.min.js', JS.'query_builder_config.js', JS.'query_builder_precan.js', JS.'query_builder_advanced.js');
                }

                $uidata->data['qb_basic'] = $basic ? 1 : 0;
                $uidata->data['qb_advanced'] = $advanced ? 1 : 0;
                $uidata->data['qb_precan'] = $precan ? 1 : 0;

                $data = $this->wrapData($uidata);
                return view("query_builder/main_precan_v2", $data);

        }
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