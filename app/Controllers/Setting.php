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



}