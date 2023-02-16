<?php
namespace App\Controllers;

/**
 * Class Auth
 *
 * @property Ion_auth|Ion_auth_model $ion_auth      The ION Auth spark
 * @package  CodeIgniter-Ion-Auth
 * @author   Ben Edmunds <ben.edmunds@gmail.com>
 * @author   Benoit VRIGNAUD <benoit.vrignaud@zaclys.net>
 * @author   Mehdi Mehtarizadeh
 * @author   Gregory Warren
 * @license  https://opensource.org/licenses/MIT	MIT License
 */

use App\Libraries\CafeVariome\Auth\LocalAuthenticator;
use App\Libraries\CafeVariome\Factory\AuthenticatorFactory;
use App\Libraries\CafeVariome\Factory\SingleSignOnProviderAdapterFactory;
use App\Models\UIData;

class Auth extends CVUIController
{
	public $provider;

	/**
	 *
	 * @var array
	 */
	public $data = [];

	/**
	 * Configuration
	 *
	 * @var \IonAuth\Config\IonAuth
	 */
	protected $configIonAuth;

	/**
	 * IonAuth library
	 *
	 * @var \IonAuth\Libraries\IonAuth
	 */
	protected $ionAuth;

	/**
	 * Session
	 *
	 * @var \CodeIgniter\Session\Session
	 */
	//private $session;

	/**
	 * Validation library
	 *
	 * @var \CodeIgniter\Validation\Validation
	 */
	private $validation;

	/**
	 * Validation list template.
	 *
	 * @var string
	 * @see https://bcit-ci.github.io/CodeIgniter4/libraries/validation.html#configuration
	 */
	protected $validationListTemplate = 'list';

	/**
	 * Views folder
	 * Set it to 'auth' if your views files are in the standard application/Views/auth
	 *
	 * @var string
	 */
	protected $viewsFolder = 'auth';


	/**
	 * Constructor
	 *
	 */
	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		parent::setProtected(false);
		parent::setIsAdmin(false);
		parent::initController($request, $response, $logger);

		$this->ionAuth = new \App\Libraries\CafeVariome\Auth\IonAuth();
		$this->validation = \Config\Services::validation();
		$this->dbAdapter = (new SingleSignOnProviderAdapterFactory())->GetInstance();
		helper(['form', 'url']);
		$this->configIonAuth = config('IonAuth');

		if (!empty($this->configIonAuth->templates['errors']['list'])) {
			$this->validationListTemplate = $this->configIonAuth->templates['errors']['list'];
		}
	}

	/**
	 * Redirect if needed, otherwise display the user list
	 *
	 * @return \CodeIgniter\HTTP\RedirectResponse
	 */
	public function Index()
	{
		return redirect()->to(base_url($this->controllerName . '/Login'));
	}

	/**
	 * Log the user in
	 *
	 * @return string|\CodeIgniter\HTTP\RedirectResponse
	 */
	public function Login()
	{
		$uidata = new UIData();

		//Check if this is a redirect from a Single Sign-on Provider
		if ($this->request->getGet('code') != null)
		{
			$token = $this->authenticator->GetAccessToken(['code' => $this->request->getGet('code')]);
			if (is_null($token))
			{
				$uidata->data['statusMessage'] = 'There was an error while trying to get an access token: ' . $this->authenticator->GetLastError();
			}
			else
			{
				//Save token in a session
				$this->session->set(self::SSO_RANDOM_STATE_SESSION, $token);
				$owner = $this->authenticator->GetResourceOwner($token);

				if (is_array($owner))
				{
					if (array_key_exists('email', $owner))
					{
						$email = $owner['email'];
						if (!array_key_exists('email_verified', $owner) || $owner['email_verified'] === false)
						{
							$uidata->data['statusMessage'] = 'User email is not verified in the single sign-on provider.';
						}
						else
						{
							$firstName = array_key_exists('given_name', $owner) ? $owner['given_name'] : null;
							$lastName = array_key_exists('family_name', $owner) ? $owner['family_name'] : null;

							// Link logged-in user to a local account and
							$userId = $this->authenticator->LinkUserToAccount(
								$email,
								$this->authenticator->GetPostAuthenticationPolicy(),
								$this->request->getIPAddress(),
								$firstName,
								$lastName
							);

							if ($userId > 0)
							{
								// Fetch user
								$user = $this->authenticator->GetUserById($userId);

								$uidata->data['statusMessage'] = '';
								if (!$user->active)
								{
									$uidata->data['statusMessage'] = 'User account is not active. You cannot login.';
								}

								if ($user->remote)
								{
									$uidata->data['statusMessage'] .= 'User account is remote. You cannot login to this installation.';
								}

								if ($user->active && !$user->remote)
								{
									$this->authenticator->UpdateLastLogin($userId);
									// Record session
									$this->authenticator->RecordSession($user);
									if ($this->session->has(self::POST_AUTHENTICATION_REDIRECT_URL_SESSION))
									{
										$redirect = $this->session->get(self::POST_AUTHENTICATION_REDIRECT_URL_SESSION);
										$this->session->remove(self::POST_AUTHENTICATION_REDIRECT_URL_SESSION);
										return redirect()->to(base_url($redirect));
									}
									return redirect()->to('Home/Index');
								}

								$this->authenticator->RemoveSession();
							}
							else
							{
								$this->authenticator->RemoveSession();
								$uidata->data['statusMessage'] = 'User could not be linked to an existing local account. Please contact the administrator.';
							}
						}
					}
				}
				else
				{
					$this->authenticator->RemoveSession();
					$uidata->data['statusMessage'] = 'There was an error while trying to get resource owner: failed to get a response in JSON format';
				}
			}
		}

		$uidata->title = 'Login';
		$uidata->data['localAuthentication'] = self::LOCAL_AUTHENTICATION;

		$uidata->data['singleSignOnProviders'] = $this->dbAdapter->ReadUserLoginSingleSignOnProviders();;

		$this->validation->setRule('provider', 'Single Sign-on Provider', 'permit_empty|integer');
		if (self::LOCAL_AUTHENTICATION)
		{
			$this->validation->setRule('identity', str_replace(':', '', lang('Auth.login_identity_label')), 'permit_empty');
			$this->validation->setRule('password', str_replace(':', '', lang('Auth.login_password_label')), 'permit_empty');
		}

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			$providerId = $this->request->getVar('provider');
			if ($providerId != null)
			{
				$singleSignOnProvider = $this->dbAdapter->Read($providerId);
				if (!$singleSignOnProvider->isNull())
				{
					$this->session->set(self::AUTHENTICATOR_SESSION, $providerId);
					$authenticator = (new AuthenticatorFactory())->GetInstance($singleSignOnProvider);

					if ($authenticator != null)
					{
						if ($this->request->getVar('code') == null)
						{
							$authUrl = $authenticator->GetAuthenticationURL();
							$this->session->set(self::SSO_RANDOM_STATE_SESSION, $authenticator->GetState());
							return redirect()->to($authUrl);
						}
						elseif(empty($this->request->getVar('state')) || ($this->request->getVar('state') !== $this->session->get(self::SSO_RANDOM_STATE_SESSION)))
						{
							$this->session->remove(self::SSO_RANDOM_STATE_SESSION);
							$uidata->data['statusMessage'] = 'Invalid state, make sure HTTP sessions are enabled.';
							return redirect()->to(base_url($this->controllerName . '/Login'));
						}
					}
				}
				else
				{
					//Provider not found
					$uidata->data['statusMessage'] = 'Single sign-on provider was not found.';
					return redirect()->to(base_url($this->controllerName . '/Login'));
				}
			}
			else
			{
				if (self::LOCAL_AUTHENTICATION)
				{
					$remember = (bool)$this->request->getVar('remember');

					if ($this->ionAuth->login($this->request->getVar('identity'), $this->request->getVar('password'), $remember))
					{
						$this->session->set(self::AUTHENTICATOR_SESSION, -1);
						$authenticator = new LocalAuthenticator();
						$user_id = $authenticator->GetUserIdByEmail($this->request->getVar('identity'));
						if (!is_null($user_id))
						{
							$user = $authenticator->GetUserById($user_id);
							if (!$user->isNull())
							{
								$authenticator->RecordSession($user);
								if ($this->session->has(self::POST_AUTHENTICATION_REDIRECT_URL_SESSION))
								{
									$redirect = $this->session->get(self::POST_AUTHENTICATION_REDIRECT_URL_SESSION);
									$this->session->remove(self::POST_AUTHENTICATION_REDIRECT_URL_SESSION);
									return redirect()->to(base_url($redirect));
								}
								return redirect()->to(base_url('Home/Index'));
							}
						}
						return redirect()->to(base_url($this->controllerName . '/Login'));
					}
					else
					{
						$this->setStatusMessage("Email or password was incorrect.", STATUS_ERROR);
						return redirect()->to(base_url($this->controllerName . '/Login'));
					}
				}
				else
				{
					//Provider Id not found
					$uidata->data['statusMessage'] = 'Single sign-on provider was not selected.';
					return redirect()->to(base_url($this->controllerName . '/Login'));
				}
			}
		}
		else
		{
			$lastMessage = '';
			if (array_key_exists('statusMessage', $uidata->data))
			{
				$lastMessage = $uidata->data['statusMessage'];
			}

			$uidata->data['statusMessage'] = $lastMessage . ($this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message'));

			if (self::LOCAL_AUTHENTICATION)
			{
				$uidata->data['identity'] = [
					'name' => 'identity',
					'id' => 'identity',
					'type' => 'text',
					'value' => set_value('identity'),
					'class' => 'form-control'

				];

				$uidata->data['password'] = [
					'name' => 'password',
					'id' => 'password',
					'type' => 'password',
					'class' => 'form-control'

				];
			}
		}

		$data = $this->wrapData($uidata);
		return view($this->controllerName . '/Login', $data);
	}

	/**
	 * Log the user out
	 *
	 */
	public function Logout()
	{
		$logoutURL = '';
		if ($this->authenticator != null)
		{
			$logoutURL = $this->authenticator->GetLogoutURL();
		}

		$this->session->destroy();


		return $logoutURL != '' ? redirect()->to($logoutURL) : redirect()->to(base_url());
	}

}
