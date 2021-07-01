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
use App\Libraries\CafeVariome\Auth\KeyCloak;
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
        parent::setIsAdmin(false);
        parent::initController($request, $response, $logger);

		$this->validation = Services::validation();
    }

    public function index(){
        return redirect()->to(base_url($this->controllerName. '/Select_Network'));
    }

    public function select_network(){
        $uidata = new UIData();
        $uidata->title = "Select Network";

        $networkInterface = new NetworkInterface();
        $networkModel = new Network();
        $sourceModel = new Source();

        $user_id = $this->session->get('user_id');

        $authorisedNetworks = [];
        $instalattionNetworks = [];

        $userNetworks = $networkModel->getNetworksUserMemberOf($user_id);
        $instalattionNtworksResp = $networkInterface->GetNetworksByInstallationKey($this->setting->getInstallationKey());

        if ($instalattionNtworksResp->status) {
            $instalattionNetworks = $instalattionNtworksResp->data;
        }

        foreach ($instalattionNetworks as $iNetwork) {
            foreach ($userNetworks as $uNetwork) {
                if ($iNetwork->network_key == $uNetwork['network_key']) {
                    array_push($authorisedNetworks, $iNetwork);
                }
            }
        }

        if (count($authorisedNetworks) == 1) {
            return redirect()->to(base_url($this->controllerName. '/query_builder/' . $authorisedNetworks[0]->network_key));
        }

        $uidata->data['networks'] = $authorisedNetworks;

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

        error_log("User: " . $this->session->get('email') . " has chosen network: $network_key || " . date("Y-m-d H:i:s"));

        $installations = [];
        $response = $networkInterface->GetInstallationsByNetworkKey((int)$network_key);

        if($response->status){
            $installations = $response->data;
        }

        $uidata->data["elasticSearchEnabled"] = true;
        $uidata->data["message"] = null;
        if (!$this->checkElasticSearch()) {
            $uidata->data["elasticSearchEnabled"] = false;
            $uidata->data["message"] = "The query builder interface is currently not accessible as Elasticsearch is not running. Please get an administrator to start Elasticsearch and then try again.";
        }

        $uidata->title = "Discover - Query Builder";
        $uidata->css = array(//VENDOR.'vakata/jstree/dist/themes/default/style.css',
                             VENDOR.'components/jqueryui/themes/base/jquery-ui.css',
                             CSS.'query_builder.css',
                             VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');

        $uidata->stickyFooter = false;

        $uidata->javascript = array(//VENDOR.'vakata/jstree/dist/jstree.js',
                                    VENDOR.'components/jqueryui/jquery-ui.js',
                                    JS.'bootstrap-notify.js',
                                    JS.'mustache.min.js',
                                    JS.'query_builder_config.js',
                                    //JS.'cafevariome/query_builder_tree.js',
                                    JS.'cafevariome/query_builder.js',
									VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js'
                                );

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
