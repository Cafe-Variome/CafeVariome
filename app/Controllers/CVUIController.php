<?php namespace App\Controllers;

/**
 * CVUI_Controller.php
 * @author:Mehdi Mehtarizadeh
 * Created: 15/06/2019
 * This class extends CodeIgniter4 Controller class.
 *
 */

use App\Libraries\CafeVariome\Auth\IAuthenticator;
use App\Libraries\CafeVariome\Auth\LocalAuthenticator;
use App\Libraries\CafeVariome\Auth\NullAuthenticator;
use App\Libraries\CafeVariome\CafeVariome;
use App\Libraries\CafeVariome\Database\IAdapter;
use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Factory\AuthenticatorFactory;
use App\Libraries\CafeVariome\Factory\SingleSignOnProviderAdapterFactory;
use CodeIgniter\Controller;
use App\Models\UIData;
use App\Models\URISegment;
use App\Libraries\CafeVariome\Net\ServiceInterface;
use CodeIgniter\Session\Session;

class CVUIController extends Controller
{
	/**
	 * @var bool if the controller is protected by authentication layer
	 */
	private bool $isProtected = false;

	/**
	 * @var bool if controller requires admin privileges
	 */
	private bool $isAdmin = false;

	/**
	 * @var bool if request is a POST
	 */
	protected bool $IsPost;

	/**
	 * @var IAdapter data adapter layer instance
	 */
	protected IAdapter $dbAdapter;

	/**
	 * @var Session session instance
	 */
	protected Session $session;

	/**
	 * @var IAuthenticator authenticator instance
	 */
	protected IAuthenticator $authenticator;

	/**
	 * @var IAdapter Setting adapter instance
	 */
	protected IAdapter $setting;

	/**
	 * @var string controller class name
	 */
	protected string $controllerName;

	/**
	 * @var string view directory name
	 */
	protected string $viewDirectory;

	/**
	 * @var ServiceInterface Service Interface object to interact with the demon
	 */
	protected ServiceInterface $serviceInterface;

	/**
	 * boolean value that determines whether internal authentication is enabled or not
	 * If set to false, OAuth will be used
	 */
	protected const LOCAL_AUTHENTICATION = ALLOW_LOCAL_AUTHENTICATION;

	/**
	 * Name of session object holding reference to authenticator object
	 */
	protected const AUTHENTICATOR_SESSION = AUTHENTICATOR_SESSION_NAME;

	/**
	 * Name of session object holding OIDC random state value
	 */
	protected const SSO_RANDOM_STATE_SESSION = SSO_RANDOM_STATE_SESSION_NAME;

	/**
	 * Name of session object holding OIDC token value
	 */
	protected const SSO_TOKEN_SESSION = SSO_TOKEN_SESSION_NAME;

	/**
	 * Name of session object holding OIDC refresh token value
	 */
	protected const SSO_REFRESH_TOKEN_SESSION = SSO_REFRESH_TOKEN_SESSION_NAME;

	/**
	 * Name of session object holding post authentication URL value
	 */
	protected const POST_AUTHENTICATION_REDIRECT_URL_SESSION = POST_AUTHENTICATION_REDIRECT_URL_SESSION_NAME;

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
        $this->setting =  CafeVariome::Settings();
		$this->serviceInterface = new ServiceInterface($this->setting->GetInstallationKey());
		$this->authenticator = new NullAuthenticator();

		if ($this->session->has(self::AUTHENTICATOR_SESSION))
		{
			if (intval($this->session->get(self::AUTHENTICATOR_SESSION)) > 0)
			{
				$authenticatorFactory = new AuthenticatorFactory();
				$provider = (new SingleSignOnProviderAdapterFactory())->GetInstance()->Read($this->session->get(self::AUTHENTICATOR_SESSION));
				if (!$provider->isNull())
				{
					$this->authenticator = $authenticatorFactory->GetInstance($provider);
				}
				else
				{
					$this->session->destroy();
				}
			}
			else
			{
				if (self::LOCAL_AUTHENTICATION)
				{
					// Local authenticator being used
					$this->authenticator = new LocalAuthenticator();
				}
			}

		}


        // If the controller needs authorisation, initiate AuthAdapter object accordingly.
		if ($this->isProtected)
		{
			$this->checkAuthentication($this->isAdmin);
		}

		$this->controllerName = $this->getClassName();
		$this->viewDirectory = $this->controllerName;
		$this->IsPost = $this->IsPost();
		$this->checkService();
		//Load form helper
		//Might be moved to a more suitable location
		helper('form');
		helper('html');
	}


	/**
	 * @param UIData $uidata
	 * @return array
	 * Wraps UIData object into an array. The array is then passed to the view.
	 */
	protected function wrapData(UIData $uidata): array
	{
		$config = new \Config\App();

		$session = \Config\Services::session($config);


		$headerImage = "";
		$headerImageFile = $this->setting->GetHeaderImage();
		if (is_file(FCPATH . HEADER_IMAGE_DIR . $headerImageFile))
		{
			if (strpos($headerImageFile, '.') !== false)
			{
				$headerImageArray = explode('.', $headerImageFile);
				$headerImageExtension = strtolower($headerImageArray[count($headerImageArray) - 1]);

				if (in_array($headerImageExtension, ['jpg', 'jpeg', 'png', 'bmp', 'svg', 'gif']))
				{
					$headerImage = HEADER_IMAGE_DIR . $headerImageFile;
				}
			}
		}

		$data = [];

		$data["site_title"] = $this->setting->GetSiteTitle();
		$data["title"] = $uidata->title;
		$data["meta_description"] = $uidata->description;
		$data["meta_keywords"] = $uidata->keywords;
		$data["meta_author"] = $uidata->author;
		$data["javascript"] = $uidata->GetJavaScript();
		$data["css"] = $uidata->GetCSS();
		$data["stickyFooter"] = $uidata->stickyFooter;
		$data['statusMessage'] = $this->getStatusMessage();
		$data['statusMessageType'] = $this->getStatusMessageTypeAlertEquivalent();
		$data["uriSegments"] = $this->getURISegments();
		$data['headerImage'] = $headerImage;
		$data['version'] = CafeVariome::GetVersion();

		//Include additional data attributes specific to each view
		foreach ($uidata->data as $dataKey => $dataValue)
		{
			$data[$dataKey] = $dataValue;
		}

		$data["session"] = &$session;
		$data["setting"] = &$setting;
		$data['controllerName'] = $this->getClassName();

		$loggedIn = $this->authenticator != null && $this->authenticator->loggedIn() &&
			!($this->authenticator->GetUserById($this->authenticator->GetUserId()) instanceof NullEntity);
		$data['loggedIn'] = $loggedIn;
		if($loggedIn)
		{
			$user = $this->authenticator->GetUserById($this->authenticator->GetUserId());
			$data['userName'] = $user->first_name;
			$data['isAdmin'] = $user->is_admin;
			$data['profileURL'] = $this->authenticator->GetProfileEndpoint();
		}

		return $data;
	}

	protected function setAuthLevel(bool $protected, bool $isAdmin)
	{
		$this->isProtected = $protected;
		$this->isAdmin = $isAdmin;
	}

	protected function setProtected(bool $protected)
	{
		$this->isProtected = $protected;
	}

	public function getProtected()
	{
		return $this->isProtected;
	}

	protected function setIsAdmin(bool $isAdmin)
	{
		$this->isAdmin = $isAdmin;
	}

	public function getAdmin()
	{
		return $this->isAdmin;
	}

	private function checkAuthentication(bool $checkIsAdmin)
	{
		$this->session->set(self::POST_AUTHENTICATION_REDIRECT_URL_SESSION, uri_string());

		if ($checkIsAdmin)
		{
			if ($this->authenticator != null && $this->authenticator->loggedIn())
			{
				if (!$this->authenticator->IsAdmin())
				{
					header('Location: '.base_url("home/index"));
					exit;
				}
			}
			else
			{
				header('Location: '.base_url("auth/login"));
				exit;
			}
		}
		else
		{
			if ($this->authenticator == null || !$this->authenticator->LoggedIn())
			{
				header('Location: '.base_url("auth/login"));
				exit;
			}
		}
	}

	protected function getURISegments(bool $lowercase = true): URISegment
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

	private function getClassName()
	{
		$className = get_class($this);

		try
		{
			$classNameArray = explode('\\', $className);
			return $classNameArray[count($classNameArray) - 1];
		}
		catch (\Throwable $th)
		{
			return $className;
		}
	}

	protected function setStatusMessage(string $message, int $msgtype, bool $appendmsg = false)
	{
		if ($appendmsg)
		{
			$msg = $this->session->getFlashData('StatusMessage');
			if ($msg)
			{
				$message = $msg . '<br/>' . $message;
			}
		}
		$this->session->setFlashData('StatusMessage', $message);
		$this->session->setFlashData('StatusMessageType', $msgtype);
	}

	protected function getStatusMessage()
	{
		return $this->session->getFlashData('StatusMessage');
	}

	protected function getStatusMessageType()
	{
		return $this->session->getFlashData('StatusMessageType');
	}

	protected function getStatusMessageTypeAlertEquivalent(): string
	{
		$msgtype = $this->getStatusMessageType();

		switch ($msgtype)
		{
			case STATUS_SUCCESS:
				return 'success';
			case STATUS_ERROR:
				return 'danger';
			case STATUS_INFO:
				return 'info';
			case STATUS_WARNING:
				return 'warning';
			default:
				return 'info';
		}
	}

	private function checkService()
	{
		if (!$this->serviceInterface->ping())
		{
			$this->serviceInterface->Start();
		}
	}

	protected function IsPost(): bool
	{
		return $this->request->getMethod() == 'post';
	}
}
