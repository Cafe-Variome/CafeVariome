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
use App\Models\URISegment;
use App\Libraries\AuthAdapter;
use CodeIgniter\Config\Services;

class CVUI_Controller extends Controller{
	
	private $isProtected = false;
	private $isAdmin = false;
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
	 * @param array     $uidata
	 *
	 * @return array
	 */

	protected function wrapData(UIData $uidata): array
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
		$data["stickyFooter"] = $uidata->stickyFooter;

		$data["uriSegments"] = $this->getURISegments();

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

		return $data;

	}	

	protected function setAuthLevel(bool $protected, bool $isAdmin){
		$this->isProtected = $protected;
		$this->isAdmin = $isAdmin;
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
			if ($this->authAdapter->loggedIn()) {
				$this->session->set('_cvReturnUrl', uri_string());
				if (!$this->authAdapter->isAdmin()) {
					header('Location: '.base_url("auth/login"));
				}
			}
			else {
				header('Location: '.base_url("auth/login"));
				exit;
			}
		} else {
			if (!$this->authAdapter->loggedIn()) {
				$this->session->set('_cvReturnUrl', uri_string());
				header('Location: '.base_url("auth/login"));
				exit;
			}
		}
		


	}

	protected function getURISegments(bool $lowercase = true)
	{
		$uri = \uri_string();
		$lowercase ? $uri = strtolower($uri) : $uri;

		$segments = explode('/', $uri);
		$uriSegments = new URISegment();

		if (count($segments) > 0) {
			$uriSegments->controllerName = $segments[0];
			if (count($segments) > 1) {
				$uriSegments->methodName = $segments[1];
				if (count($segments) > 2) {
					for ($i=2; $i < count($segments); $i++) { 
						$uriSegments->params[$i] = $segments[$i];
					}
				}
			}
		}
		return $uriSegments;
	}

}