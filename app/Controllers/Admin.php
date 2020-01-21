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
use App\Helpers\AuthHelper;

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

    function Index(){
        $uidata = new UIData();
        $uidata->title = "Administrator Dashboard";
        $uidata->stickyFooter = false;

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory. '/Index', $data);
    }


    function Settings() {
        $uidata = new UIData();
        $uidata->title = "Settings";
        $uidata->stickyFooter = false;

        $settingModel = new Settings($this->db, true);

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
            foreach ($settings as $s) {
                $settingModel->updateSettings(['value' => $this->request->getVar($s["setting_key"])], ['setting_key' =>  $s["setting_key"]]);
            }
            return redirect()->to(base_url($this->controllerName.'/Settings'));
        }
        else{
            $uidata->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
        }

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory. '/Settings', $data);
    }
}