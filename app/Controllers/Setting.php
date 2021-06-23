<?php namespace App\Controllers;

/**
 * Setting.php
 * Created 05/05/2021
 *
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 */

use App\Models\UIData;
use App\Models\Settings;
use App\Models\User;
use App\Libraries\ElasticSearch;
use App\Libraries\CafeVariome\Auth\KeyCloak;
use App\Models\NetworkRequest;
use App\Libraries\CafeVariome\Net\NetworkInterface;
use App\Libraries\CafeVariome\Net\ServiceInterface;
use CodeIgniter\Config\Services;

class Setting extends CVUI_Controller{

    private $settingModel;

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
        $this->setting =  Settings::getInstance();

        $this->settingModel = Settings::getInstance();
    }

    function Authentication()
    {
        $uidata = new UIData();
        $uidata->title = "Authentication Settings";
        $uidata->stickyFooter = false;

        $this->settingModel = Settings::getInstance();

        $settings =  $this->settingModel->getSettingsByGroup('authentication');
        $uidata->data['settings'] = $settings;

        if ($this->request->getPost()) {
            $this->processPost($settings);

            return redirect()->to(base_url($this->controllerName.'/Authentication'));
        }

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory. '/Authentication', $data);
    }

    public function Discovery()
    {
        $uidata = new UIData();
        $uidata->title = "Discovery Settings";
        $uidata->stickyFooter = false;

        $this->settingModel = Settings::getInstance();

        $settings =  $this->settingModel->getSettingsByGroup('discovery');
        $uidata->data['settings'] = $settings;

        if ($this->request->getPost()) {
            $this->processPost($settings);

            return redirect()->to(base_url($this->controllerName.'/Discovery'));
        }

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory. '/Discovery', $data);
    }

    public function Endpoint()
    {
        $uidata = new UIData();
        $uidata->title = "Endpoint Settings";
        $uidata->stickyFooter = false;

        $this->settingModel = Settings::getInstance();

        $settings =  $this->settingModel->getSettingsByGroup('endpoint');
        $uidata->data['settings'] = $settings;

        if ($this->request->getPost()) {
            $this->processPost($settings);

            return redirect()->to(base_url($this->controllerName.'/Endpoint'));
        }

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory. '/Endpoint', $data);
    }

    public function Main()
    {
        $uidata = new UIData();
        $uidata->title = "Main System Settings";
        $uidata->stickyFooter = false;

        $this->settingModel = Settings::getInstance();

        $settings =  $this->settingModel->getSettingsByGroup('main');
        $uidata->data['settings'] = $settings;

        if ($this->request->getPost()) {
            $this->processPost($settings);

            return redirect()->to(base_url($this->controllerName.'/Main'));
        }

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory. '/Main', $data);
    }

    public function Elasticsearch()
    {
        $uidata = new UIData();
        $uidata->title = "Elastic Search Settings";
        $uidata->stickyFooter = false;

        $this->settingModel = Settings::getInstance();

        $settings =  $this->settingModel->getSettingsByGroup('elasticsearch');
        $uidata->data['settings'] = $settings;

        if ($this->request->getPost()) {
            $this->processPost($settings);

            return redirect()->to(base_url($this->controllerName.'/Elasticsearch'));
        }

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory. '/Elasticsearch', $data);
    }

    public function Neo4J()
    {
        $uidata = new UIData();
        $uidata->title = "Neo4J Settings";
        $uidata->stickyFooter = false;

        $this->settingModel = Settings::getInstance();

        $settings =  $this->settingModel->getSettingsByGroup('neo4j');
        $uidata->data['settings'] = $settings;

        if ($this->request->getPost()) {
            $this->processPost($settings);

            return redirect()->to(base_url($this->controllerName.'/Neo4J'));
        }

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory. '/Neo4J', $data);
    }

    private function processPost(array $settings)
    {
        $errorFlag = false;
        foreach ($settings as $s) {
            $settingName = $s['setting_name'];
            $settingKey = $s["setting_key"];
            $settingVal = trim($this->request->getVar($settingKey));
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
                if ($this->setting->settingData[$s["setting_key"]] == 'on' || $this->setting->settingData[$s["setting_key"]] == 'off') {
                    $settingVal = $settingVal == null ? 'off' : 'on';
                }
                try {
                    $this->settingModel->updateSettings(['value' => $settingVal], ['setting_key' =>  $settingKey]);
                } catch (\Exception $ex) {
                    $errorFlag = true;
                    $this->setStatusMessage("There was a problem updating '$settingName'.", STATUS_ERROR);
                }
            }
        }

        if (!$errorFlag) {
            $this->setStatusMessage("Settings were updated.", STATUS_SUCCESS);
        }
    }

}
