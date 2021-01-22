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

use App\Models\UIData;
use App\Models\Settings;
use App\Helpers\AuthHelper;
use App\Libraries\CafeVariome\Auth\AuthAdapter;
class Auth extends CVUI_Controller
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
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger){
        parent::setProtected(false);
        parent::setIsAdmin(false);
        parent::initController($request, $response, $logger);

		$this->ionAuth    = new \App\Libraries\CafeVariome\Auth\IonAuth();
		$this->validation = \Config\Services::validation();
		helper(['form', 'url']);
		$this->configIonAuth = config('IonAuth');

		if (! empty($this->configIonAuth->templates['errors']['list']))
		{
			$this->validationListTemplate = $this->configIonAuth->templates['errors']['list'];
		}	
    }

	/**
	 * Redirect if needed, otherwise display the user list
	 *
	 * @return \CodeIgniter\HTTP\RedirectResponse
	 */
	public function index()
	{
		if (!$this->authAdapter->loggedIn())
		{
			return redirect()->to(base_url("Auth/login"));
		}
		else
		{
			return redirect()->to(base_url("Home/index"));
		}
	}

	/**
	 * Log the user in
	 *
	 * @return string|\CodeIgniter\HTTP\RedirectResponse
	 */
	public function login()
	{	
		if($this->authAdapter->loggedIn())
		{
			if ($this->session->has('_cvReturnUrl')) {
				$redirect = $this->session->get('_cvReturnUrl');
				$this->session->remove('_cvReturnUrl');
				return redirect()->to(base_url($redirect));
			}
			return redirect()->to(base_url('Home/index'));
		}
		else{
			if($this->authAdapter->login('', '', false)){
				if ($this->session->has('_cvReturnUrl')) {
					$redirect = $this->session->get('_cvReturnUrl');
					$this->session->remove('_cvReturnUrl');
					return redirect()->to(base_url($redirect));
				}
				return redirect()->to(base_url('Home/index'));
			}
			$uidata = new UIData();
			$uidata->data['message'] = "Authentication server is not available. Only local login is possible at the moment.";

			$uidata->title = lang('Auth.login_heading');
	
			//$uidata->javascript = array(JS."cafevariome/authentication.js");
			
			// validate form input
			$this->validation->setRule('identity', str_replace(':', '', lang('Auth.login_identity_label')), 'required');
			$this->validation->setRule('password', str_replace(':', '', lang('Auth.login_password_label')), 'required');
			
			if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
			{
				// check to see if the user is logging in
				// check for "remember me"
				$remember = (bool)$this->request->getVar('remember');

				if ($this->ionAuth->login($this->request->getVar('identity'), $this->request->getVar('password'), $remember))
				{
					return redirect()->to(base_url('Home/Index'));
				}
				else
				{
					$this->setStatusMessage("Email or password was incorrect.", STATUS_ERROR);
					return redirect()->to(base_url('auth/login'));
				}
				$data = $this->wrapData($uidata);
				return view('auth/login', $data);
			}
			else
			{
				// the user is not logging in so display the login page
				// set the flash data error message if there is one
				$uidata->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
				
				$uidata->data['identity'] = [
					'name'  => 'identity',
					'id'    => 'identity',
					'type'  => 'text',
					'value' => set_value('identity'),
					'class' => 'form-control'
	
				];
	
				$uidata->data['password'] =[
					'name' => 'password',
					'id'   => 'password',
					'type' => 'password',
					'class' => 'form-control'
	
				];
	
				$data = $this->wrapData($uidata);
				return view($this->viewsFolder . DIRECTORY_SEPARATOR . 'login', $data);
			}			
		}	
	}

	public function klogin(){
		$l = $this->authAdapter->login('', '', false);

		if($l){

			if ($this->session->has('_cvReturnUrl')) {
				$redirect = $this->session->get('_cvReturnUrl');
				$this->session->remove('_cvReturnUrl');
				header('Location: '.base_url( $redirect));
				exit;
			}
			else if($this->authAdapter->isAdmin()){
				header('Location: '.base_url('Home/index'));
				exit;	
			}
			header('Location: '.base_url('home'));
			exit;
		}
		else{			
			header('Location: '.base_url('auth/login'));
			exit;
		}

	}
	/**
	 * Log the user out
	 *
	 * @return \CodeIgniter\HTTP\RedirectResponse
	 */
	public function logout()
	{
		$this->authAdapter->logout();

		$this->data['title'] = 'Logout';
		// log the user out
		//$this->ionAuth->logout();

		// redirect them to the login page
		$this->session->setFlashdata('message', $this->ionAuth->messages());
		return redirect()->to(base_url('auth/login'));
	}

	/**
	 * Change password
	 *
	 * @return string|\CodeIgniter\HTTP\RedirectResponse
	 */
	public function change_password()
	{
		$this->validation->setRule('old', lang('Auth.change_password_validation_old_password_label'), 'required');
		$this->validation->setRule('new', lang('Auth.change_password_validation_new_password_label'), 'required|min_length[' . $this->configIonAuth->minPasswordLength . ']|matches[new_confirm]');
		$this->validation->setRule('new_confirm', lang('Auth.change_password_validation_new_password_confirm_label'), 'required');

		if (! $this->ionAuth->loggedIn())
		{
			return redirect()->to(base_url('/auth/login'));
		}

		$user = $this->ionAuth->user()->row();

		if ($this->validation->run() === false)
		{
			// display the form
			// set the flash data error message if there is one
			$this->data['message'] = ($this->validation->getErrors()) ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

			$this->data['minPasswordLength'] = $this->configIonAuth->minPasswordLength;
			$this->data['old_password'] = [
				'name' => 'old',
				'id'   => 'old',
				'type' => 'password',
			];
			$this->data['new_password'] = [
				'name'    => 'new',
				'id'      => 'new',
				'type'    => 'password',
				'pattern' => '^.{' . $this->data['minPasswordLength'] . '}.*$',
			];
			$this->data['new_password_confirm'] = [
				'name'    => 'new_confirm',
				'id'      => 'new_confirm',
				'type'    => 'password',
				'pattern' => '^.{' . $this->data['minPasswordLength'] . '}.*$',
			];
			$this->data['user_id'] = [
				'name'  => 'user_id',
				'id'    => 'user_id',
				'type'  => 'hidden',
				'value' => $user->id,
			];

			// render
			return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'change_password', $this->data);
		}
		else
		{
			$identity = $this->session->userdata('identity');

			$change = $this->ionAuth->changePassword($identity, $this->request->getPost('old'), $this->request->getPost('new'));

			if ($change)
			{
				//if the password was successfully changed
				$this->session->setFlashdata('message', $this->ionAuth->messages());
				$this->logout();
			}
			else
			{
				$this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
				return redirect()->to(base_url('/auth/change_password'));
			}
		}
	}

	/**
	 * Forgot password
	 *
	 * @return string|\CodeIgniter\HTTP\RedirectResponse
	 */
	public function forgot_password()
	{
		$this->data['title'] = lang('Auth.forgot_password_heading');

		// setting validation rules by checking whether identity is username or email
		if ($this->configIonAuth->identity !== 'email')
		{
			$this->validation->setRule('identity', lang('Auth.forgot_password_identity_label'), 'required');
		}
		else
		{
			$this->validation->setRule('identity', lang('Auth.forgot_password_validation_email_label'), 'required|valid_email');
		}

		if (! ($this->request->getPost() && $this->validation->withRequest($this->request)->run()))
		{
			$this->data['type'] = $this->configIonAuth->identity;
			// setup the input
			$this->data['identity'] = [
				'name' => 'identity',
				'id'   => 'identity',
			];

			if ($this->configIonAuth->identity !== 'email')
			{
				$this->data['identity_label'] = lang('Auth.forgot_password_identity_label');
			}
			else
			{
				$this->data['identity_label'] = lang('Auth.forgot_password_email_identity_label');
			}

			// set any errors and display the form
			$this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
			return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'forgot_password', $this->data);
		}
		else
		{
			$identityColumn = $this->configIonAuth->identity;
			$identity = $this->ionAuth->where($identityColumn, $this->request->getPost('identity'))->users()->row();

			if (empty($identity))
			{
				if ($this->configIonAuth->identity !== 'email')
				{
					$this->ionAuth->setError('Auth.forgot_password_identity_not_found');
				}
				else
				{
					$this->ionAuth->setError('Auth.forgot_password_email_not_found');
				}

				$this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
				return redirect()->to(base_url('/auth/forgot_password'));
			}

			// run the forgotten password method to email an activation code to the user
			$forgotten = $this->ionAuth->forgottenPassword($identity->{$this->configIonAuth->identity});

			if ($forgotten)
			{
				// if there were no errors
				$this->session->setFlashdata('message', $this->ionAuth->messages());
				return redirect()->to(base_url('/auth/login')); //we should display a confirmation page here instead of the login page
			}
			else
			{
				$this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
				return redirect()->to(base_url('/auth/forgot_password'));
			}
		}
	}

	/**
	 * Reset password - final step for forgotten password
	 *
	 * @param string|null $code The reset code
	 *
	 * @return string|\CodeIgniter\HTTP\RedirectResponse
	 */
	public function reset_password($code = null)
	{
		if (! $code)
		{
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$this->data['title'] = lang('Auth.reset_password_heading');

		$user = $this->ionAuth->forgottenPasswordCheck($code);

		if ($user)
		{
			// if the code is valid then display the password reset form

			$this->validation->setRule('new', lang('Auth.reset_password_validation_new_password_label'), 'required|min_length[' . $this->configIonAuth->minPasswordLength . ']|matches[new_confirm]');
			$this->validation->setRule('new_confirm', lang('Auth.reset_password_validation_new_password_confirm_label'), 'required');

			if (! $this->request->getPost() || $this->validation->withRequest($this->request)->run() === false)
			{
				// display the form

				// set the flash data error message if there is one
				$this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

				$this->data['minPasswordLength'] = $this->configIonAuth->minPasswordLength;
				$this->data['new_password'] = [
					'name'    => 'new',
					'id'      => 'new',
					'type'    => 'password',
					'pattern' => '^.{' . $this->data['minPasswordLength'] . '}.*$',
				];
				$this->data['new_password_confirm'] = [
					'name'    => 'new_confirm',
					'id'      => 'new_confirm',
					'type'    => 'password',
					'pattern' => '^.{' . $this->data['minPasswordLength'] . '}.*$',
				];
				$this->data['user_id'] = [
					'name'  => 'user_id',
					'id'    => 'user_id',
					'type'  => 'hidden',
					'value' => $user->id,
				];
				$this->data['code'] = $code;

				// render
				return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'reset_password', $this->data);
			}
			else
			{
				$identity = $user->{$this->configIonAuth->identity};

				// do we have a valid request?
				if ($user->id != $this->request->getPost('user_id'))
				{
					// something fishy might be up
					$this->ionAuth->clearForgottenPasswordCode($identity);

					throw new \Exception(lang('Auth.error_security'));
				}
				else
				{
					// finally change the password
					$change = $this->ionAuth->resetPassword($identity, $this->request->getPost('new'));

					if ($change)
					{
						// if the password was successfully changed
						$this->session->setFlashdata('message', $this->ionAuth->messages());
						return redirect()->to(base_url('/auth/login'));
					}
					else
					{
						$this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
						return redirect()->to(base_url('/auth/reset_password/' . $code));
					}
				}
			}
		}
		else
		{
			// if the code is invalid then send them back to the forgot password page
			$this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
			return redirect()->to(base_url('/auth/forgot_password'));
		}
	}

	/**
	 * Activate the user
	 *
	 * @param integer $id   The user ID
	 * @param string  $code The activation code
	 *
	 * @return \CodeIgniter\HTTP\RedirectResponse
	 */
	public function activate(int $id, string $code = ''): \CodeIgniter\HTTP\RedirectResponse
	{
		$activation = false;

		if (! $code)
		{
			$activation = $this->ionAuth->activate($id, $code);
		}
		else if ($this->ionAuth->isAdmin())
		{
			$activation = $this->ionAuth->activate($id);
		}

		if ($activation)
		{
			// redirect them to the auth page
			$this->session->setFlashdata('message', $this->ionAuth->messages());
			return redirect()->to(base_url('/auth'));
		}
		else
		{
			// redirect them to the forgot password page
			$this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
			return redirect()->to(base_url('/auth/forgot_password'));
		}
	}

	/**
	 * Deactivate the user
	 *
	 * @param integer $id The user ID
	 *
	 * @throw Exception
	 *
	 * @return string|\CodeIgniter\HTTP\RedirectResponse
	 */
	public function deactivate(int $id = 0)
	{
		if (! $this->ionAuth->loggedIn() || ! $this->ionAuth->isAdmin())
		{
			// redirect them to the home page because they must be an administrator to view this
			throw new \Exception('You must be an administrator to view this page.');
			// TODO : I think it could be nice to have a dedicated exception like '\IonAuth\Exception\NotAllowed
		}

		$this->validation->setRule('confirm', lang('Auth.deactivate_validation_confirm_label'), 'required');
		$this->validation->setRule('id', lang('Auth.deactivate_validation_user_id_label'), 'required|integer');

		if (! $this->validation->withRequest($this->request)->run())
		{
			$this->data['user'] = $this->ionAuth->user($id)->row();
			return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'deactivate_user', $this->data);
		}
		else
		{
			// do we really want to deactivate?
			if ($this->request->getPost('confirm') === 'yes')
			{
				// do we have a valid request?
				if ($id !== $this->request->getPost('id', FILTER_VALIDATE_INT))
				{
					throw new \Exception(lang('Auth.error_security'));
				}

				// do we have the right userlevel?
				if ($this->ionAuth->loggedIn() && $this->ionAuth->isAdmin())
				{
					$message = $this->ionAuth->deactivate($id) ? $this->ionAuth->messages() : $this->ionAuth->errors($this->validationListTemplate);
					$this->session->setFlashdata('message', $message);
				}
			}

			// redirect them back to the auth page
			return redirect()->to(base_url('/auth'));
		}
	}

	/**
	 * Create a new user
	 *
	 * @return string|\CodeIgniter\HTTP\RedirectResponse
	 */
	public function create_user()
	{
		$this->data['title'] = lang('Auth.create_user_heading');

		if (! $this->ionAuth->loggedIn() || ! $this->ionAuth->isAdmin())
		{			
			return redirect()->to(base_url("auth"));
		}

		$tables                        = $this->configIonAuth->tables;
		$identityColumn                = $this->configIonAuth->identity;
		$this->data['identity_column'] = $identityColumn;

		// validate form input
		$this->validation->setRule('first_name', lang('Auth.create_user_validation_fname_label'), 'trim|required');
		$this->validation->setRule('last_name', lang('Auth.create_user_validation_lname_label'), 'trim|required');
		if ($identityColumn !== 'email')
		{
			$this->validation->setRule('identity', lang('Auth.create_user_validation_identity_label'), 'trim|required|is_unique[' . $tables['users'] . '.' . $identityColumn . ']');
			$this->validation->setRule('email', lang('Auth.create_user_validation_email_label'), 'trim|required|valid_email');
		}
		else
		{
			$this->validation->setRule('email', lang('Auth.create_user_validation_email_label'), 'trim|required|valid_email|is_unique[' . $tables['users'] . '.email]');
		}
		$this->validation->setRule('phone', lang('Auth.create_user_validation_phone_label'), 'trim');
		$this->validation->setRule('company', lang('Auth.create_user_validation_company_label'), 'trim');
		$this->validation->setRule('password', lang('Auth.create_user_validation_password_label'), 'required|min_length[' . $this->configIonAuth->minPasswordLength . ']|matches[password_confirm]');
		$this->validation->setRule('password_confirm', lang('Auth.create_user_validation_password_confirm_label'), 'required');

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			$email    = strtolower($this->request->getPost('email'));
			$identity = ($identityColumn === 'email') ? $email : $this->request->getPost('identity');
			$password = $this->request->getPost('password');

			$additionalData = [
				'first_name' => $this->request->getPost('first_name'),
				'last_name'  => $this->request->getPost('last_name'),
				'company'    => $this->request->getPost('company'),
				'phone'      => $this->request->getPost('phone'),
			];
		}
		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run() && $this->ionAuth->register($identity, $password, $email, $additionalData))
		{
			// check to see if we are creating the user
			// redirect them back to the admin page
			$this->session->setFlashdata('message', $this->ionAuth->messages());
			return redirect()->to(base_url('/auth'));
		}
		else
		{
			// display the create user form
			// set the flash data error message if there is one
			$this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : ($this->ionAuth->errors($this->validationListTemplate) ? $this->ionAuth->errors($this->validationListTemplate) : $this->session->getFlashdata('message'));

			$this->data['first_name'] = [
				'name'  => 'first_name',
				'id'    => 'first_name',
				'type'  => 'text',
				'value' => set_value('first_name'),
			];
			$this->data['last_name'] = [
				'name'  => 'last_name',
				'id'    => 'last_name',
				'type'  => 'text',
				'value' => set_value('last_name'),
			];
			$this->data['identity'] = [
				'name'  => 'identity',
				'id'    => 'identity',
				'type'  => 'text',
				'value' => set_value('identity'),
			];
			$this->data['email'] = [
				'name'  => 'email',
				'id'    => 'email',
				'type'  => 'email',
				'value' => set_value('email'),
			];
			$this->data['company'] = [
				'name'  => 'company',
				'id'    => 'company',
				'type'  => 'text',
				'value' => set_value('company'),
			];
			$this->data['phone'] = [
				'name'  => 'phone',
				'id'    => 'phone',
				'type'  => 'text',
				'value' => set_value('phone'),
			];
			$this->data['password'] = [
				'name'  => 'password',
				'id'    => 'password',
				'type'  => 'password',
				'value' => set_value('password'),
			];
			$this->data['password_confirm'] = [
				'name'  => 'password_confirm',
				'id'    => 'password_confirm',
				'type'  => 'password',
				'value' => set_value('password_confirm'),
			];

			return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'create_user', $this->data);
		}
	}

	/**
	 * Redirect a user checking if is admin
	 *
	 * @return \CodeIgniter\HTTP\RedirectResponse
	 */
	public function redirectUser()
	{
		if ($this->ionAuth->isAdmin())
		{
			return redirect()->to(base_url('/auth'));
		}
		return redirect()->to(base_url());
	}

	/**
	 * Edit a user
	 *
	 * @param integer $id User id
	 *
	 * @return string string|\CodeIgniter\HTTP\RedirectResponse
	 */
	public function edit_user(int $id)
	{
		$uidata = new UIData();
		$uidata->title = "Profile";
		$uidata->stickyFooter = false;
		if (! $this->ionAuth->loggedIn() || (! $this->ionAuth->isAdmin() && ! ($this->ionAuth->user()->row()->id == $id)))
		{
			return redirect()->to(base_url('/auth'));
		}

		$user          = $this->ionAuth->user($id)->row();
		$groups        = $this->ionAuth->groups()->resultArray();
		$currentGroups = $this->ionAuth->getUsersGroups($id)->getResult();

		if (! empty($_POST))
		{
			// validate form input
			$this->validation->setRule('first_name', lang('Auth.edit_user_validation_fname_label'), 'trim|required');
			$this->validation->setRule('last_name', lang('Auth.edit_user_validation_lname_label'), 'trim|required');
			$this->validation->setRule('phone', lang('Auth.edit_user_validation_phone_label'), 'trim|required');
			$this->validation->setRule('company', lang('Auth.edit_user_validation_company_label'), 'trim|required');

			// do we have a valid request?
			if ($id !== $this->request->getPost('id', FILTER_VALIDATE_INT))
			{
				//show_error(lang('Auth.error_security'));
				throw new \Exception(lang('Auth.error_security'));
			}

			// update the password if it was posted
			if ($this->request->getPost('password'))
			{
				$this->validation->setRule('password', lang('Auth.edit_user_validation_password_label'), 'required|min_length[' . $this->configIonAuth->minPasswordLength . ']|matches[password_confirm]');
				$this->validation->setRule('password_confirm', lang('Auth.edit_user_validation_password_confirm_label'), 'required');
			}

			if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
			{
				$data = [
					'first_name' => $this->request->getPost('first_name'),
					'last_name'  => $this->request->getPost('last_name'),
					'company'    => $this->request->getPost('company'),
					'phone'      => $this->request->getPost('phone'),
				];

				// update the password if it was posted
				if ($this->request->getPost('password'))
				{
					$data['password'] = $this->request->getPost('password');
				}

				// Only allow updating groups if user is admin
				if ($this->ionAuth->isAdmin())
				{
					// Update the groups user belongs to
					$groupData = $this->request->getPost('groups');

					if (! empty($groupData))
					{
						$this->ionAuth->removeFromGroup('', $id);

						foreach ($groupData as $grp)
						{
							$this->ionAuth->addToGroup($grp, $id);
						}
					}
				}

				// check to see if we are updating the user
				if ($this->ionAuth->update($user->id, $data))
				{
					$this->session->setFlashdata('message', $this->ionAuth->messages());
				}
				else
				{
					$this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
				}
				// redirect them back to the admin page if admin, or to the base url if non admin
				return redirect()->to(base_url('home/index'));
			}
		}

		// display the edit user form

		// set the flash data error message if there is one
		//$uidata->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : ($this->ionAuth->errors($this->validationListTemplate) ? $this->ionAuth->errors($this->validationListTemplate) : $this->session->getFlashdata('message'));
		$uidata->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) :  $this->session->getFlashdata('message');

		// pass the user to the view
		$uidata->data['user']          = $user;
		$uidata->data['groups']        = $groups;
		$uidata->data['currentGroups'] = $currentGroups;

		$uidata->data['first_name'] = [
			'name'  => 'first_name',
			'id'    => 'first_name',
			'type'  => 'text',
			'value' => set_value('first_name', $user->first_name ?: ''),
			'class' => 'form-control'
		];
		$uidata->data['last_name'] = [
			'name'  => 'last_name',
			'id'    => 'last_name',
			'type'  => 'text',
			'value' => set_value('last_name', $user->last_name ?: ''),
			'class' => 'form-control'
		];
		$uidata->data['company'] = [
			'name'  => 'company',
			'id'    => 'company',
			'type'  => 'text',
			'value' => set_value('company', empty($user->company) ? '' : $user->company),
			'class' => 'form-control'
		];
		$uidata->data['phone'] = [
			'name'  => 'phone',
			'id'    => 'phone',
			'type'  => 'text',
			'value' => set_value('phone', empty($user->phone) ? '' : $user->phone),
			'class' => 'form-control'
		];
		$uidata->data['password'] = [
			'name' => 'password',
			'id'   => 'password',
			'type' => 'password',
			'class' => 'form-control'
		];
		$uidata->data['password_confirm'] = [
			'name' => 'password_confirm',
			'id'   => 'password_confirm',
			'type' => 'password',
			'class' => 'form-control'
		];
		$uidata->data['ionAuth'] = $this->ionAuth;

		$data = $this->wrapData($uidata);

		return view('auth/edit_user', $data);
		//return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'edit_user', $this->data);
	}

	/**
	 * Create a new group
	 *
	 * @return string string|\CodeIgniter\HTTP\RedirectResponse
	 */
	public function create_group()
	{
		$this->data['title'] = lang('Auth.create_group_title');

		if (! $this->ionAuth->loggedIn() || ! $this->ionAuth->isAdmin())
		{
			return redirect()->to(base_url('/auth'));
		}

		// validate form input
		$this->validation->setRule('group_name', lang('Auth.create_group_validation_name_label'), 'trim|required|alpha_dash');

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			$newGroupId = $this->ionAuth->createGroup($this->request->getPost('group_name'), $this->request->getPost('description'));
			if ($newGroupId)
			{
				// check to see if we are creating the group
				// redirect them back to the admin page
				$this->session->setFlashdata('message', $this->ionAuth->messages());
				return redirect()->to(base_url('/auth'));
			}
		}
		else
		{
			// display the create group form
			// set the flash data error message if there is one
			$this->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : ($this->ionAuth->errors($this->validationListTemplate) ? $this->ionAuth->errors($this->validationListTemplate) : $this->session->getFlashdata('message'));

			$this->data['group_name'] = [
				'name'  => 'group_name',
				'id'    => 'group_name',
				'type'  => 'text',
				'value' => set_value('group_name'),
			];
			$this->data['description'] = [
				'name'  => 'description',
				'id'    => 'description',
				'type'  => 'text',
				'value' => set_value('description'),
			];

			return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'create_group', $this->data);
		}
	}

	/**
	 * Edit a group
	 *
	 * @param integer $id Group id
	 *
	 * @return string|CodeIgniter\Http\Response
	 */
	public function edit_group(int $id = 0)
	{
		// bail if no group id given
		if (! $id)
		{
			return redirect()->to(base_url('/auth'));
		}

		$this->data['title'] = lang('Auth.edit_group_title');

		if (! $this->ionAuth->loggedIn() || ! $this->ionAuth->isAdmin())
		{
			return redirect()->to(base_url('/auth'));
		}

		$group = $this->ionAuth->group($id)->row();

		// validate form input
		$this->validation->setRule('group_name', lang('Auth.edit_group_validation_name_label'), 'required|alpha_dash');

		if ($this->request->getPost())
		{
			if ($this->validation->withRequest($this->request)->run())
			{
				$groupUpdate = $this->ionAuth->updateGroup($id, $this->request->getPost('group_name'), ['description' => $this->request->getPost('group_description')]);

				if ($groupUpdate)
				{
					$this->session->setFlashdata('message', lang('Auth.edit_group_saved'));
				}
				else
				{
					$this->session->setFlashdata('message', $this->ionAuth->errors($this->validationListTemplate));
				}
				return redirect()->to(base_url('/auth'));
			}
		}

		// set the flash data error message if there is one
		$this->data['message'] = $this->validation->listErrors($this->validationListTemplate) ?: ($this->ionAuth->errors($this->validationListTemplate) ?: $this->session->getFlashdata('message'));

		// pass the user to the view
		$this->data['group'] = $group;

		$readonly = $this->configIonAuth->adminGroup === $group->name ? 'readonly' : '';

		$this->data['group_name']        = [
			'name'    => 'group_name',
			'id'      => 'group_name',
			'type'    => 'text',
			'value'   => set_value('group_name', $group->name),
			$readonly => $readonly,
		];
		$this->data['group_description'] = [
			'name'  => 'group_description',
			'id'    => 'group_description',
			'type'  => 'text',
			'value' => set_value('group_description', $group->description),
		];

		return $this->renderPage($this->viewsFolder . DIRECTORY_SEPARATOR . 'edit_group', $this->data);
	}

	/**
	 * Render the specified view
	 *
	 * @param string     $view       The name of the file to load
	 * @param array|null $data       An array of key/value pairs to make available within the view.
	 * @param boolean    $returnHtml If true return html string
	 *
	 * @return string|void
	 */
	protected function renderPage(string $view, $data = null, bool $returnHtml = true): string
	{
		$viewdata = $data ?: $this->data;

		$viewHtml = view($view, $viewdata);

		if ($returnHtml)
		{
			return $viewHtml;
		}
		else
		{
			echo $viewHtml;
		}
	}


}
