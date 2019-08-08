<?php namespace App\Controllers;

/**
 * CVUI_Controller.php
 * @author:Mehdi Mehtarizadeh
 * Created: 15/06/2019
 * This class extends CodeIgniter4 Controller class.
 * 
 */
use CodeIgniter\Controller;
use App\Models\UIData;
use App\Models\cms_model;
use App\Models\Settings;
use App\Libraries\AuthAdapter;
use CodeIgniter\Config\Services;

class CVUI_Controller extends Controller{
	
	private $isProtected;
	private $isAdmin;
	protected $db;

	protected $session;
	protected $authAdapter;
	protected $setting;

	private $authAdapterConfig;

	/**
	 * Constructor.
	 *
	 */
	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);
		//--------------------------------------------------------------------
		// Preload any models, libraries, etc, here.
		//--------------------------------------------------------------------
		// E.g.:
		// $this->session = \Config\Services::session();
		$this->session = \Config\Services::session();
		$this->db = \Config\Database::connect();
        $this->setting =  Settings::getInstance($this->db);

        // If the controller needs authorisation, initiate AuthAdapater object accordingly.
		if ($this->isProtected) {
			$this->authAdapterConfig = config('AuthAdapter');
			$this->authAdapter = new AuthAdapter($this->authAdapterConfig->authRoutine);

			$this->checkAuthentication($this->isAdmin);
		}


		//Load form helper
		//Might be moved to a more suitable location
		helper('form');
		helper('html');

	}


	/**
	 * Wraps UIData object into an array. The array is then passed to the view.
	 *
	 * @param string     $uidata
	 *
	 * @return array
	 */

	protected function wrapData($uidata)
	{
		$config = new \Config\App();

		$session = \Config\Services::session($config);
		$setting =  Settings::getInstance($this->db);
		
        $authAdapterConfig = config('AuthAdapter');
        $authAdapter = new AuthAdapter($authAdapterConfig->authRoutine);
		$data = Array();

		$data["title"] = $uidata->title;
		$data["description"] = $uidata->description;
		$data["keywords"] = $uidata->keywords;
		$data["author"] = $uidata->author;

		$data["javascript"] = $uidata->javascript;
		$data["css"] = $uidata->css;

		//Include additional data attributes specific to each view
		foreach ($uidata->data as $dataKey => $dataValue) {
			$data[$dataKey] = $dataValue;
		}

		$data["session"] = &$session;
		$data["setting"] = &$setting;
		$data["auth"] = &$authAdapter;
		//Moved to CI4 by Mehdi Mehtarizadeh(mm876) on 18/06/2019 

		//Get dynamic menus from CMS from database and pass to nav template
		$cmsModel = new Cms_model($this->db);
		$data['cmsModel'] = &$cmsModel;

		/**
		 * ToDo: Make the below snippet compatible with CI4 as the authentication model is added. 
		 */
		/*
		if ($this->config->item('messaging')) {
			if ($this->ion_auth->logged_in() ) {
				$this->load->model('messages_model');
				$user_id = $this->session->userdata('user_id');
				$unread_messages = $this->messages_model->get_message_count($user_id);
				$toMenu['unread_messages'] = $unread_messages;
			}
		}
		

		$toHeader["basejs"] = view("template/basejs",$this->data,true);
		
		$toBody["header"] = view("template/header",$toHeader,true);
		$toBody["footer"] = view("template/footer",'',true);
		
		$toTpl["body"] = view("template/".$this->template,$toBody,true);
		*/

		return $data;

	}	

	protected function setProtected(bool $protected){
		$this->isProtected = $protected;
	}

	public function getProtected(){
		return $this->isProtected;
	}

	protected function setIsAdmin(bool $isAdmin){
		$this->isAdmin = $isAdmin;
	}

	public function getAdmin(){
		return $this->isAdmin;
	}

	private function checkAuthentication(bool $checkIsAdmin) {
		if ($checkIsAdmin) {
			if (!$this->authAdapter->loggedIn()) {
				if (!$this->authAdapter->isAdmin()) {
					header('Location: '.base_url("daeisadeghhhhhh"));
				}
				header('Location: '.base_url("auth/login"));
				exit;
			}
		} else {
			if (!$this->authAdapter->loggedIn()) {
				header('Location: '.base_url("auth/login"));
				exit;
			}
		}
		


	}

}