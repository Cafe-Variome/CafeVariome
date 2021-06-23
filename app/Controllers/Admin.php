<?php namespace App\Controllers;

/**
 * Admin.php
 * Created 18/07/2019
 *
 * @author Owen Lancaster
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 */

use App\Models\UIData;
use App\Models\Settings;
use App\Models\Network;
use App\Models\Source;
use App\Models\User;
use App\Libraries\ElasticSearch;
use App\Libraries\CafeVariome\Core\DataPipeLine\Stream\Neo4J;
use App\Libraries\CafeVariome\Auth\KeyCloak;
use App\Models\NetworkRequest;
use App\Libraries\CafeVariome\Net\NetworkInterface;
use App\Libraries\CafeVariome\Net\ServiceInterface;
use CodeIgniter\Config\Services;

class Admin extends CVUI_Controller{

    /**
	 * Validation list template.
	 *
	 * @var string
	 */
    protected $validationListTemplate = 'list';


    /**
	 * Constructor
	 *
	 */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger){
        parent::setProtected(true);
        parent::setIsAdmin(true);
        parent::initController($request, $response, $logger);

		$this->session = Services::session();
		$this->db = \Config\Database::connect();
        $this->setting =  Settings::getInstance($this->db);

        $this->validation = Services::validation();

    }

    public function Index(){
        $uidata = new UIData();
        $uidata->title = "Administrator Dashboard";
        $uidata->stickyFooter = false;
        $uidata->css = [CSS.'dashboard/chartjs/Chart.min.css'];
        $uidata->javascript = [JS.'dashboard/chartjs/Chart.min.js'];

        $sourceModel = new Source();
        $networkInterface = new NetworkInterface();
        $userModel = new User();
        $networkRequestModel = new NetworkRequest();

        $elasticSearch = new ElasticSearch(array($this->setting->getElasticSearchUri()));
        $neo4j = new Neo4J();
        $keyCloak = new KeyCloak();
        $service = new ServiceInterface();

        $sourceList = $sourceModel->getSources('source_id, name', ['status'=>'online']);

        $sc = 0;
        $maxSourcesToDisplay = 12;
        $sourceCountList = [];
        $sourceNameLabels = '';
        foreach ($sourceList as $source) {
            if ($sc > $maxSourcesToDisplay) {
                break;
            }
            if ($sc == count($sourceList) - 1 || $sc == $maxSourcesToDisplay) {
                $sourceNameLabels .= "'" . $source['name']. "'";
            }
            else{
                $sourceNameLabels .= "'" . $source['name']. "',";
            }

            $sourceCountList[$source['name']] = 0;

            $sc++;
        }

        $uidata->data['sourceCount'] = count($sourceList);
        $uidata->data['sourceNames'] = $sourceNameLabels;

        $networks = $networkInterface->GetNetworksByInstallationKey($this->setting->getInstallationKey());
        if ($networks->status) {
            $uidata->data['networksCount'] = count($networks->data);
            $uidata->data['networkMsg'] = null;
        }
        else{
            //Problem contacting network server
            $uidata->data['networksCount'] = "-";
            $uidata->data['networkMsg'] = "There was a problem in communicating with network software. Please fix it as the system is unable to function correctly.";
        }

        $uidata->data['usersCount'] = count($userModel->getUsers('id'));
        $uidata->data['networkRequestCount'] = count($networkRequestModel->getNetworkRequests('id', ['status' => -1]));

        $elasticStatus = $elasticSearch->ping();
        $uidata->data['elasticStatus'] = $elasticStatus;
        $uidata->data['elasticMsg'] = null;
        if (!$elasticStatus) {
            $uidata->data['elasticMsg'] = "Elasticsearch is not running. The query interface is not accessible. Please ask the server administrator to start it.";
        }

        $neo4jStatus = $neo4j->ping();
        $uidata->data['neo4jStatus'] = $neo4jStatus;
        $uidata->data['neo4jMsg'] = null;
        if (!$neo4jStatus) {
            $uidata->data['neo4jMsg'] = "Neo4J is not running. Some capabilities of the system are disabled because of this. Please ask the server administrator to start it.";
        }

        $uidata->data['keycloakStatus'] = $keyCloak->ping();
        $uidata->data['serviceStatus'] = $service->ping();



        $data = $this->wrapData($uidata);
        return view($this->viewDirectory. '/Index', $data);
    }


    function Settings() {
        $uidata = new UIData();
        $uidata->title = "Settings";
        $uidata->stickyFooter = false;

        $settingModel = Settings::getInstance();

        $settings =  $settingModel->getSettings();
        $uidata->data['settings'] = $settings;
        /*
        $validationRules = [];

        foreach ($settings as $s) {
            $validationRules[$s['setting_key']] = [
                'label' => $s['setting_name'],
                'rules' => $s['validation_rules'],
                'errors' => [

                ]
            ];
        }

        $this->validation->setRules($validationRules);
        */

        if ($this->request->getPost() /*&& $this->validation->withRequest($this->request)->run()*/) {
            $errorFlag = false;
            foreach ($settings as $s) {
                $settingName = $s['setting_name'];
                $settingKey = $s["setting_key"];
                $settingVal = $this->request->getVar($settingKey);
                if ($settingVal != $this->setting->settingData[$s["setting_key"]]) {
                    if ($settingKey == 'installation_key') {
                        $settingVal = trim($settingVal);
                    }
                    if ($settingKey == 'auth_server') {
                        $settingVal = trim($settingVal);
                        $valLen = strlen($settingVal);
                        if(substr($settingVal, $valLen-1, $valLen) != '/'){
                            $settingVal = $settingVal . '/';
                        }
                    }
                    try {
                        $settingModel->updateSettings(['value' => $settingVal], ['setting_key' =>  $settingKey]);
                    } catch (\Exception $ex) {
                        $errorFlag = true;
                        $this->setStatusMessage("There was a problem updating '$settingName'.", STATUS_ERROR);
                    }
                }
            }
            if (!$errorFlag) {
                $this->setStatusMessage("Settings were updated.", STATUS_SUCCESS);
            }

            return redirect()->to(base_url($this->controllerName.'/Settings'));
        }
        // else{
        //     $uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
        // }

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory. '/Settings', $data);
    }
}
