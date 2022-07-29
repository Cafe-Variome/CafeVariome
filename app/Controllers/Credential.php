<?php namespace App\Controllers;

/**
 * Credential.php
 * Created 03/05/2022
 *
 * This class offers CRUD operation for Credential.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Factory\CredentialAdapterFactory;
use App\Libraries\CafeVariome\Factory\CredentialFactory;
use App\Models\UIData;
use CodeIgniter\Config\Services;

class Credential extends CVUI_Controller
{

	private $validation;

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
	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		parent::setProtected(true);
		parent::setIsAdmin(true);
		parent::initController($request, $response, $logger);

		$this->validation = Services::validation();
		$this->dbAdapter = (new CredentialAdapterFactory())->GetInstance();

	}

	public function Index()
	{
		return redirect()->to(base_url($this->controllerName . '/List'));
	}

	public function List()
	{
		$uidata = new UIData();
		$uidata->title = 'Credentials';

		$credentials = $this->dbAdapter->ReadAll();

		$uidata->data['credentials'] = $credentials;
		$uidata->css = [VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css'];
		$uidata->javascript = [
			JS. 'cafevariome/credential.js',
			VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js'
		];

		$data = $this->wrapData($uidata);
		return view($this->controllerName . '/List', $data);
	}

	public function Create()
	{
		$uidata = new UIData();
		$uidata->title = 'Create Credential';

		$this->validation->setRules([
			'name' => [
				'label'  => 'Name',
				'rules'  => 'required|alpha_numeric_punct|max_length[128]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_numeric_punct' => 'The only valid characters for {field} are alphabetical characters, numbers, and some punctuation characters.',
					'max_length' => 'Maximum length is 128 characters.'
				]
			],
			'username' => [
				'label'  => 'Username',
				'rules'  => 'string|max_length[128]',
				'errors' => [
					'string' => '{field} must be a valid string.',
					'max_length' => 'Maximum length is 128 characters.'
				]
			],
			'password' => [
				'label'  => 'Password',
				'rules'  => 'string|max_length[128]',
				'errors' => [
					'string' => '{field} must be a valid string.',
					'max_length' => 'Maximum length is 128 characters.'
				]
			],
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			try
			{
				$name = $this->request->getVar('name');
				$username = $this->request->getVar('username');
				$password = $this->request->getVar('password');
				$hide_username = $this->request->getVar('hide_username') ? true : false;

				$this->dbAdapter->Create((new CredentialFactory())->getInstanceFromParameters($name, $username, $password, $hide_username));

				$this->setStatusMessage("Credential '$name' was created.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem creating credential." . $ex->getMessage(), STATUS_ERROR);
			}

			return redirect()->to(base_url($this->controllerName . '/List'));
		}
		else
		{
			$uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

			$uidata->data['name'] = array(
				'name' => 'name',
				'id' => 'name',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('name'),
			);

			$uidata->data['username'] = array(
				'name' => 'username',
				'id' => 'username',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('username'),
			);

			$uidata->data['password'] = array(
				'name' => 'password',
				'id' => 'password',
				'type' => 'password',
				'class' => 'form-control',
				'value' =>set_value('password'),
			);

			$uidata->data['hide_username'] = array(
				'name' => 'hide_username[]',
				'id' => 'hide_username',
				'type' => 'checkbox',
				'checked' => false,
				'value' =>set_value('hide_username'),
			);
		}

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Create', $data);
	}

	public function Update(int $id)
	{
		$credential = $this->dbAdapter->Read($id);

		if ($credential->isNull())
		{
			$this->setStatusMessage("Credential was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$uidata = new UIData();
		$uidata->title = 'Edit Credential';
		$uidata->data['id'] = $credential->getID();
		$uidata->javascript = [JS. 'cafevariome/credential.js'];

		$this->validation->setRules([
			'name' => [
				'label'  => 'Name',
				'rules'  => 'required|alpha_numeric_punct|max_length[128]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_numeric_punct' => 'The only valid characters for {field} are alphabetical characters, numbers, and some punctuation characters.',
					'max_length' => 'Maximum length is 128 characters.'
				]
			],
			'username' => [
				'label'  => 'Username',
				'rules'  => 'permit_empty|string|max_length[128]',
				'errors' => [
					'string' => '{field} must be a valid string.',
					'max_length' => 'Maximum length is 128 characters.'
				]
			],
			'password' => [
				'label'  => 'Password',
				'rules'  => 'permit_empty|string|max_length[128]',
				'errors' => [
					'string' => '{field} must be a valid string.',
					'max_length' => 'Maximum length is 128 characters.'
				]
			],
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			try
			{
				$name = $this->request->getVar('name');
				$username = $this->request->getVar('username');
				$username_changed = $this->request->getVar('username_changed');
				$hide_username = $this->request->getVar('hide_username') ? true : false;
				$password = $this->request->getVar('password');
				$password_changed = $this->request->getVar('password_changed');
				$encrypt = $username_changed || $password_changed;

				$this->dbAdapter->Update(
					$id,
					(new CredentialFactory())->getInstanceFromParameters($name, $username_changed ? $username : null, $password_changed ? $password : null, $hide_username, $credential->hash, $encrypt)
				);

				$this->setStatusMessage("Credential '$name' was updated.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem updating credential" . $ex->getMessage(), STATUS_ERROR);
			}

			return redirect()->to(base_url($this->controllerName . '/List'));
		}
		else
		{
			$uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

			$uidata->data['name'] = array(
				'name' => 'name',
				'id' => 'name',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('name', $credential->name),
			);

			$uidata->data['username'] = array(
				'name' => 'username',
				'id' => 'username',
				'type' => 'text',
				'class' => 'form-control',
				'placeholder' => $credential->hide_username ? '[Username hidden]' : '',
				'disabled' => 'disabled',
				'onchange' => 'usernameChange()',
				'value' =>set_value('username', $credential->hide_username ? '' : $credential->username),
			);

			$uidata->data['username_changed'] = array(
				'type' => 'hidden',
				'name' => 'username_changed',
				'id' => 'username_changed',
				'value' =>set_value('username_changed', 'false'),
			);

			$uidata->data['password'] = array(
				'name' => 'password',
				'id' => 'password',
				'type' => 'password',
				'class' => 'form-control',
				'placeholder' => '***********',
				'disabled' => 'disabled',
				'onchange' => 'passwordChange()',
				'value' =>set_value('password'),
			);

			$uidata->data['password_changed'] = array(
				'type' => 'hidden',
				'name' => 'password_changed',
				'id' => 'password_changed',
				'value' =>set_value('password_changed', 'false'),
			);

			$uidata->data['hide_username'] = array(
				'name' => 'hide_username[]',
				'id' => 'hide_username',
				'type' => 'checkbox',
				'checked' => (bool)$credential->hide_username,
				'value' =>set_value('hide_username', (string)$credential->hide_username),
			);
		}

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Update', $data);
	}

	public function Details(int $id)
	{
		$credential = $this->dbAdapter->Read($id);

		if ($credential->isNull())
		{
			$this->setStatusMessage("Credential was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$uidata = new UIData();
		$uidata->title = 'Credential Details';
		$uidata->data['credential'] = $credential;

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Details', $data);
	}

	public function Delete(int $id)
	{
		$credential = $this->dbAdapter->Read($id);

		if ($credential->isNull())
		{
			$this->setStatusMessage("Credential was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$uidata = new UIData();
		$uidata->title = 'Delete Credential';
		$uidata->data['credential'] = $credential;

		$this->validation->setRules([
			'confirm' => [
				'label'  => 'confirmation',
				'rules'  => 'required',
				'errors' => [
					'required' => '{field} is required.'
				]
			],

			'id' => [
				'label'  => 'Id',
				'rules'  => 'required|integer',
				'errors' => [
					'required' => '{field} is required.',
					'integer' => '{field} must be a positive and non-zero integer.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			try
			{
				$confirm = $this->request->getVar('confirm');
				if ($confirm == 'yes')
				{
					$this->dbAdapter->Delete($id);
					$this->setStatusMessage("Credential was deleted.", STATUS_SUCCESS);
				}
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem deleting credential: " . $ex->getMessage(), STATUS_ERROR);
			}

			return redirect()->to(base_url($this->controllerName . '/List'));
		}
		else
		{
			$uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

		}

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory . '/Delete', $data);
	}
}
