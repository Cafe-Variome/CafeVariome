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
    }

    function index(){
        $uidata = new UIData();
        $uidata->title = "Administrator Dashboard";


        $data = $this->wrapData($uidata);
        return view("Admin/Index", $data);
    }


    function settings() {
        $uidata = new UIData();
        $uidata->title = "Settings";

        $settings = $this->setting->settingData;
        var_dump($settings);exit;
        $uidata->data['settings'] = $settings;

        $data = $this->wrapData($uidata);
        return view("admin/settings", $data);
    }



}