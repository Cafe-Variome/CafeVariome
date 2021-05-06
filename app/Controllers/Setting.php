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
use App\Libraries\Neo4J;
use App\Libraries\CafeVariome\Auth\KeyCloak;
use App\Models\NetworkRequest;
use App\Helpers\AuthHelper;
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