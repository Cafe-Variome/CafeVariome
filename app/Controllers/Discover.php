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
use App\Models\Network;
use App\Libraries\KeyCloak;
use App\Libraries\CafeVariome;
use App\Libraries\CafeVariome\Query;
use App\Helpers\AuthHelper;
use CodeIgniter\Config\Services;
use App\Libraries\CafeVariome\Net\NetworkInterface;
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

    public function index(){
        return redirect()->to(base_url($this->controllerName. '/Select_Network'));
    }

    public function select_network(){
        $uidata = new UIData();   
        $uidata->title = "Select Network";

        $networkModel = new Network($this->db);
        $sourceModel = new Source($this->db);

        $user_id = $this->session->get('user_id');
        $networks = $networkModel->getNetworksUserMemberOf($user_id);

        $uidata->data['networks'] = array();

        foreach ($networks as $key => $value) {
            $uidata->data['networks'] += array($value['name'] => $value['network_key']);
        }
        
        $uidata->javascript = array(JS."cafevariome/discover.js");

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory.'/Select_Network', $data);
    }

    public function query_builder($network_key = null){
        
        $uidata = new UIData();
        $networkInterface = new NetworkInterface();

        if ($network_key) {
            $this->session->set(array('network_key' => $network_key));
        } 
        else {
            return redirect()->to(base_url($this->controllerName. '/Select_Network'));
        }

        // Check if the user is in the master network group for this network
        
        $user_id = $this->authAdapter->getUserId();
        
        $uidata->data['user_id'] = $user_id;
        $uidata->data['network_key'] = $network_key;
        
        $token = $this->session->get('Token');

        //$installation_urls = json_decode(AuthHelper::authPostRequest(array('installation_key' => $this->setting->settingData['installation_key'], 'network_key' => $network_key), $this->setting->settingData['auth_server'] . "network/get_all_installation_ips_for_network"), true);
        //$data = AuthHelper::authPostRequest(array('installation_key' => $this->setting->settingData['installation_key']), $this->setting->settingData['auth_server'] . "network/get_all_installations_for_network");
        
        $installations = [];
        $response = $networkInterface->GetInstallationsByNetworkKey((int)$network_key);

        if($response->status){
            $installations = $response->data;
        }
        //$data = stripslashes($data);
        //$data = json_decode($data, 1);

        // Set the federated installs in the session so they can be used by variantcount
        //$this->session->set(array('federated_installs' => $installations['installation_urls']));
        //$this->session->set(array('network_threshold' => $installations['network_threshold']));

        $uidata->data["elasticSearchEnabled"] = true;
        $uidata->data["message"] = null;
        if (!$this->checkElasticSearch()) {
            $uidata->data["elasticSearchEnabled"] = false;
            $uidata->data["message"] = "The query builder interface is currently not accessible as Elasticsearch is not running. Please get an administrator to start Elasticsearch and then try again.";
        }


        $uidata->title = "Discover - Query Builder";
        $uidata->css = array(VENDOR.'vakata/jstree/dist/themes/default/style.css', VENDOR.'components/jqueryui/themes/base/jquery-ui.css', CSS.'jquery.querybuilder.css');  

        $uidata->stickyFooter = false;

        $uidata->javascript = array(VENDOR.'vakata/jstree/dist/jstree.js', VENDOR.'components/jqueryui/jquery-ui.js',JS.'bootstrap-notify.js', JS.'typeaheadjs/dist/typeahead.bundle.min.js', JS.'mustache.min.js', JS.'query_builder_config.js', JS.'query_builder.js');
        $data = $this->wrapData($uidata);
        return view($this->viewDirectory. '/Query_Builder', $data);
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