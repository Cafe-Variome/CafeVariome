<?php namespace App\Controllers;


/**
 * SingleSignOnProvider.php
 * Created 13/05/2022
 *
 * This class offers CRUD operation for SingleSignOnProvider.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Core\IO\FileSystem\UploadFileMan;
use App\Libraries\CafeVariome\Factory\CredentialAdapterFactory;
use App\Libraries\CafeVariome\Factory\EntityFactory;
use App\Libraries\CafeVariome\Factory\ProxyServerAdapterFactory;
use App\Libraries\CafeVariome\Factory\ServerAdapterFactory;
use App\Libraries\CafeVariome\Factory\SingleSignOnProviderAdapterFactory;
use App\Libraries\CafeVariome\Factory\SingleSignOnProviderFactory;
use App\Libraries\CafeVariome\Helpers\UI\SingleSignOnProviderHelper;
use App\Models\UIData;
use CodeIgniter\Config\Services;

class SingleSignOnProvider extends CVUIController
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
		$this->dbAdapter = (new SingleSignOnProviderAdapterFactory())->GetInstance();
	}

	public function Index()
	{
		return redirect()->to(base_url($this->controllerName . '/List'));
	}

	public function List()
	{
		$uidata = new UIData();
		$uidata->title = 'Single Sign-on Providers';

		$uidata->data['singleSignOnProviders'] = $this->dbAdapter->ReadAll();

		$uidata->IncludeJavaScript(JS. 'cafevariome/singlesignonprovider.js');
		$uidata->IncludeDataTables();

		$data = $this->wrapData($uidata);
		return view($this->controllerName . '/List', $data);
	}

	public function Create()
	{
		$uidata = new UIData();
		$uidata->title = 'Create Single Sign-on Provider';
		$maximumAllowedUploadSize = '512K';
		$uidata->data['maxUploadSize'] = UploadFileMan::parseSizeToByte($maximumAllowedUploadSize);
		$uidata->data['maxUploadSizeH'] = $maximumAllowedUploadSize;
		$uidata->IncludeJavaScript(JS. 'cafevariome/singlesignonprovider.js');

		$serverAdapterFactory = new ServerAdapterFactory();
		$servers = $serverAdapterFactory->GetInstance()->ReadAll();
		$serversList = [-1 => 'Please select a server...'];

		foreach ($servers as $server)
		{
			$serversList[$server->getID()] = $server->name . ' [' . $server->address . ']';
		}

		$credentialAdapterFactory = new CredentialAdapterFactory();
		$credentials = $credentialAdapterFactory->GetInstance()->ReadAll();
		$credentialsList = [-1 => 'Please select a credential if necessary...'];

		foreach ($credentials as $credential)
		{
			if (!empty($credential->username ))
			{
				$credentialsList[$credential->getID()] = $credential->name . ($credential->hide_username ? ' [Username is hidden]' : ' [' . $credential->username . ']');
			}
		}

		$proxyServerAdapterFactory = new ProxyServerAdapterFactory();
		$proxyServers = $proxyServerAdapterFactory->GetInstance()->ReadAll();
		$proxyServerList = [-1 => 'Please select a proxy server if necessary...'];

		foreach ($proxyServers as $proxyServer)
		{
			$proxyServerList[$proxyServer->getID()] = $proxyServer->name;
		}

		$maximumAllowedUploadSize = UploadFileMan::getMaximumAllowedUploadSize();

		$this->validation->setRules([
			'name' => [
				'label'  => 'Name',
				'rules'  => 'required|alpha_numeric_punct|max_length[128]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_numeric_punct' => 'The only valid characters for {field} are alphabetical characters, numbers, and some punctuation characters.',
					'max_length' => 'Maximum length of {field} is 128 characters.'
				]
			],
			'display_name' => [
				'label'  => 'Display Name',
				'rules'  => 'required|alpha_numeric_punct|max_length[256]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_numeric_punct' => 'The only valid characters for {field} are alphabetical characters, numbers, and some punctuation characters.',
					'max_length' => 'Maximum length of {field} is 256 characters.'
				]
			],
			'server_id' => [
				'label'  => 'Server',
				'rules'  => 'required|integer|greater_than_equal_to[1]|max_length[11]',
				'errors' => [
					'required' => '{field} is required.',
					'integer' => '{field} can only be a positive integer.',
					'greater_than_equal_to' => 'Please select a {field}.',
					'max_length' => 'Maximum length of {field} is 11 digits.'
				]
			],
			'port' => [
				'label'  => 'Port',
				'rules'  => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[65535]|max_length[5]',
				'errors' => [
					'required' => '{field} is required.',
					'integer' => '{field} can only be a positive integer.',
					'greater_than_equal_to' => '{field} must be an integer greater than or equal to 0.',
					'less_than_equal_to' => '{field} must be an integer less than or equal to 65,535.',
					'max_length' => 'Maximum length of {field} is 5 digits.'
				]
			],
			'credential_id' => [
				'label'  => 'Credential',
				'rules'  => 'required|integer|greater_than_equal_to[-1]|max_length[11]',
				'errors' => [
					'required' => '{field} is required.',
					'integer' => '{field} can only be a positive integer.',
					'greater_than_equal_to' => 'Please select a {field}.',
					'max_length' => 'Maximum length of {field} is 11 digits.'
				]
			],
			'proxy_server_id' => [
				'label'  => 'Proxy Server',
				'rules'  => 'required|integer|greater_than_equal_to[-1]|max_length[11]',
				'errors' => [
					'required' => '{field} is required.',
					'integer' => '{field} can only be a positive integer.',
					'greater_than_equal_to' => 'Please select a {field}.',
					'max_length' => 'Maximum length of {field} is 11 digits.'
				]
			],
			'type' => [
				'label'  => 'Type',
				'rules'  => 'integer|greater_than_equal_to[0]|max_length[3]',
				'errors' => [
					'integer' => '{field} must be a positive integer.',
					'greater_than_equal_to' => 'Please select a valid {field}.',
					'max_length' => 'Maximum length of {field} is 3 digits.'
				]
			],
			'icon' => [
				'label'  => 'Icon',
				'rules'  => "permit_empty|mime_in[icon,image/png,image/jpg]|is_image[icon]|max_size[icon,$maximumAllowedUploadSize]",
				'errors' => [
					'is_image' => '{field} must be a valid image.',
					'max_size' => '{field} file size must be less than ' . UploadFileMan::parseSizeToByte($maximumAllowedUploadSize) . '.',
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			try
			{
				// Check if icon file is present
				$basePath = FCPATH . UPLOAD . UPLOAD_ICONS;
				$fileMan = new UploadFileMan($basePath, true, 27);
				$files = $fileMan->getFiles();

				$icon = null;
				if (count($files) == 1)
				{
					//Upload icon
					if($fileMan->Save($files[0]))
					{
						$icon = $files[0]->getDiskName();
					}
				}

				$name = $this->request->getVar('name');
				$display_name = $this->request->getVar('display_name');
				$server_id = $this->request->getVar('server_id');
				$port = $this->request->getVar('port');
				$credential_id = $this->request->getVar('credential_id') > 0 ? $this->request->getVar('credential_id') : null;
				$proxy_server_id = $this->request->getVar('proxy_server_id') > 0 ? $this->request->getVar('proxy_server_id') : null;
				$type = $this->request->getVar('type');
				$query = $this->request->getVar('query') ? true : false;
				$user_login = $this->request->getVar('user_login') ? true : false;
				$authentication_policy = $this->request->getVar('authentication_policy');

				$this->dbAdapter->Create((new SingleSignOnProviderFactory())->GetInstanceFromParameters(
					$name, $display_name, $type, $port, $authentication_policy, $query, $user_login, $server_id, $credential_id, $proxy_server_id, $icon
				));

				$this->setStatusMessage("Single SignOn Provider $name was created.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem creating Single SignOn Provider: " . $ex->getMessage(), STATUS_ERROR);
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

			$uidata->data['display_name'] = array(
				'name' => 'display_name',
				'id' => 'display_name',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('display_name'),
			);

			$uidata->data['icon'] = array(
				'name' => 'icon',
				'id' => 'icon',
				'type' => 'file',
				'class' => 'form-control',
				'aria-describedby' => 'icon',
				'value' =>set_value('icon'),
			);

			$uidata->data['type'] = array(
				'name' => 'type',
				'id' => 'type',
				'type' => 'dropdown',
				'class' => 'form-select',
				'value' =>set_value('type'),
				'options' => [
					SINGLE_SIGNON_OIDC2 => SingleSignOnProviderHelper::getType(SINGLE_SIGNON_OIDC2),
					//SINGLE_SIGNON_SAML2 => SingleSignOnProviderHelper::getType(SINGLE_SIGNON_SAML2)
				]
			);

			$uidata->data['server_id'] = array(
				'name' => 'server_id',
				'id' => 'server_id',
				'type' => 'dropdown',
				'class' => 'form-select',
				'value' => set_value('server_id'),
				'options' => $serversList
			);

			$uidata->data['port'] = array(
				'name' => 'port',
				'id' => 'port',
				'type' => 'text',
				'class' => 'form-control',
				'value' => set_value('port'),
			);

			$uidata->data['credential_id'] = array(
				'name' => 'credential_id',
				'id' => 'credential_id',
				'type' => 'dropdown',
				'class' => 'form-select',
				'value' => set_value('credential_id'),
				'options' => $credentialsList
			);

			$uidata->data['proxy_server_id'] = array(
				'name' => 'proxy_server_id',
				'id' => 'proxy_server_id',
				'type' => 'dropdown',
				'class' => 'form-select',
				'value' =>set_value('proxy_server_id'),
				'options' => $proxyServerList
			);

			$uidata->data['user_login'] = array(
				'name' => 'user_login[]',
				'id' => 'user_login',
				'type' => 'checkbox',
				'class' => 'form-check-input',
				'checked' => false,
				'value' =>is_array($user_login_vl = set_value('user_login')) ? $user_login_vl[0] : set_value('user_login'),
			);

			$uidata->data['query'] = array(
				'name' => 'query[]',
				'id' => 'query',
				'type' => 'checkbox',
				'class' => 'form-check-input',
				'checked' => false,
				'value' =>is_array($query_vl = set_value('query')) ? $query_vl[0] : set_value('query'),
			);

			$uidata->data['authentication_policy'] = array(
				'name' => 'authentication_policy',
				'id' => 'authentication_policy',
				'class' => 'form-select',
				'type' => 'dropdown',
				'value' =>set_value('authentication_policy'),
				'options' => [
					SINGLE_SIGNON_POST_AUTH_CREATE_ACCOUNT => SingleSignOnProviderHelper::getPostAuthenticanPolicy(SINGLE_SIGNON_POST_AUTH_CREATE_ACCOUNT),
					SINGLE_SIGNON_POST_AUTH_LINK_ACCOUNT => SingleSignOnProviderHelper::getPostAuthenticanPolicy(SINGLE_SIGNON_POST_AUTH_LINK_ACCOUNT)
				]
			);

		}

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Create', $data);
	}

	public function Update(int $id)
	{
		$singleSignOnProvider = $this->dbAdapter->Read($id);

		if ($singleSignOnProvider->isNull())
		{
			$this->setStatusMessage("Single Sign-on Provider was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$uidata = new UIData();
		$uidata->title = 'Edit Single Sign-on Provider';
		$uidata->data['id'] = $singleSignOnProvider->getID();
		$uidata->data['singleSignOnProvider'] = $singleSignOnProvider;
		$maximumAllowedUploadSize = '512K';
		$uidata->data['maxUploadSize'] = UploadFileMan::parseSizeToByte($maximumAllowedUploadSize);
		$uidata->data['maxUploadSizeH'] = $maximumAllowedUploadSize;
		$uidata->IncludeJavaScript(JS. 'cafevariome/singlesignonprovider.js');

		$serverAdapterFactory = new ServerAdapterFactory();
		$servers = $serverAdapterFactory->GetInstance()->ReadAll();
		$serversList = [-1 => 'Please select a server...'];

		foreach ($servers as $server)
		{
			$serversList[$server->getID()] = $server->name . ' [' . $server->address . ']';
		}

		$credentialAdapterFactory = new CredentialAdapterFactory();
		$credentials = $credentialAdapterFactory->GetInstance()->ReadAll();
		$credentialsList = [-1 => 'Please select a credential if necessary...'];

		foreach ($credentials as $credential)
		{
			$credentialsList[$credential->getID()] = $credential->name . ($credential->hide_username ? ' [Username is hidden]' : ' [' . $credential->username . ']');
		}

		$proxyServerAdapterFactory = new ProxyServerAdapterFactory();
		$proxyServers = $proxyServerAdapterFactory->GetInstance()->ReadAll();
		$proxyServerList = [-1 => 'Please select a proxy server if necessary...'];

		foreach ($proxyServers as $proxyServer)
		{
			$proxyServerList[$proxyServer->getID()] = $proxyServer->name;
		}

		$maximumAllowedUploadSize = UploadFileMan::getMaximumAllowedUploadSize();

		$this->validation->setRules([
			'name' => [
				'label'  => 'Name',
				'rules'  => 'required|alpha_numeric_punct|max_length[128]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_numeric_punct' => 'The only valid characters for {field} are alphabetical characters, numbers, and some punctuation characters.',
					'max_length' => 'Maximum length of {field} is 128 characters.'
				]
			],
			'display_name' => [
				'label'  => 'Display Name',
				'rules'  => 'required|alpha_numeric_punct|max_length[256]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_numeric_punct' => 'The only valid characters for {field} are alphabetical characters, numbers, and some punctuation characters.',
					'max_length' => 'Maximum length of {field} is 256 characters.'
				]
			],
			'server_id' => [
				'label'  => 'Server',
				'rules'  => 'required|integer|greater_than_equal_to[1]|max_length[11]',
				'errors' => [
					'required' => '{field} is required.',
					'integer' => '{field} can only be a positive integer.',
					'greater_than_equal_to' => 'Please select a {field}.',
					'max_length' => 'Maximum length of {field} is 11 digits.'
				]
			],
			'port' => [
				'label'  => 'Port',
				'rules'  => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[65535]|max_length[5]',
				'errors' => [
					'required' => '{field} is required.',
					'integer' => '{field} can only be a positive integer.',
					'greater_than_equal_to' => '{field} must be an integer greater than or equal to 0.',
					'less_than_equal_to' => '{field} must be an integer less than or equal to 65,535.',
					'max_length' => 'Maximum length of {field} is 5 digits.'
				]
			],
			'credential_id' => [
				'label'  => 'Credential',
				'rules'  => 'required|integer|greater_than_equal_to[-1]|max_length[11]',
				'errors' => [
					'required' => '{field} is required.',
					'integer' => '{field} can only be a positive integer.',
					'greater_than_equal_to' => 'Please select a {field}.',
					'max_length' => 'Maximum length of {field} is 11 digits.'
				]
			],
			'proxy_server_id' => [
				'label'  => 'Proxy Server',
				'rules'  => 'required|integer|greater_than_equal_to[-1]|max_length[11]',
				'errors' => [
					'required' => '{field} is required.',
					'integer' => '{field} can only be a positive integer.',
					'greater_than_equal_to' => 'Please select a {field}.',
					'max_length' => 'Maximum length of {field} is 11 digits.'
				]
			],
			'type' => [
				'label'  => 'Type',
				'rules'  => 'integer|greater_than_equal_to[0]|max_length[3]',
				'errors' => [
					'integer' => '{field} must be a positive integer.',
					'greater_than_equal_to' => 'Please select a valid {field}.',
					'max_length' => 'Maximum length of {field} is 3 digits.'
				]
			],
			'icon' => [
				'label'  => 'Icon',
				'rules'  => "permit_empty|mime_in[icon,image/png,image/jpg]|is_image[icon]|max_size[icon,$maximumAllowedUploadSize]",
				'errors' => [
					'is_image' => '{field} must be a valid image.',
					'max_size' => '{field} file size must be less than ' . UploadFileMan::parseSizeToByte($maximumAllowedUploadSize) . '.',
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			try
			{
				// Check if icon file is present
				$basePath = FCPATH . UPLOAD . UPLOAD_ICONS;
				$fileMan = new UploadFileMan($basePath, true, 27);
				$files = $fileMan->getFiles();

				$icon = $singleSignOnProvider->icon;
				if (count($files) == 1)
				{
					if ($icon != null)
					{
						//Remove old icon
						if ($fileMan->Exists($icon))
						{
							$fileMan->Delete($icon);
						}
					}

					//Upload icon
					if($fileMan->Save($files[0]))
					{
						$icon = $files[0]->getDiskName();
					}
				}

				$name = $this->request->getVar('name');
				$display_name = $this->request->getVar('display_name');
				$server_id = $this->request->getVar('server_id');
				$port = $this->request->getVar('port');
				$credential_id = $this->request->getVar('credential_id') > 0 ? $this->request->getVar('credential_id') : null;
				$proxy_server_id = $this->request->getVar('proxy_server_id') > 0 ? $this->request->getVar('proxy_server_id') : null;
				$type = $this->request->getVar('type');
				$query = $this->request->getVar('query') ? true : false;
				$user_login = $this->request->getVar('user_login') ? true : false;
				$authentication_policy = $this->request->getVar('authentication_policy');

				$this->dbAdapter->Update($id, (new SingleSignOnProviderFactory())->GetInstanceFromParameters(
					$name, $display_name, $type, $port, $authentication_policy, $query, $user_login, $server_id, $credential_id, $proxy_server_id, $icon
				));

				$this->setStatusMessage("Single Sign-on Provider '$name' was updated.", STATUS_SUCCESS);

			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem updating single sign-on provider: " . $ex->getMessage(), STATUS_ERROR);
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
				'value' =>set_value('name', $singleSignOnProvider->name),
			);

			$uidata->data['display_name'] = array(
				'name' => 'display_name',
				'id' => 'display_name',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('display_name', $singleSignOnProvider->display_name),
			);

			$uidata->data['icon'] = array(
				'name' => 'icon',
				'id' => 'icon',
				'type' => 'file',
				'class' => 'form-control',
				'aria-describedby' => 'icon',
				'value' =>set_value('icon'),
			);

			$uidata->data['type'] = array(
				'name' => 'type',
				'id' => 'type',
				'type' => 'dropdown',
				'class' => 'form-select',
				'value' =>set_value('type'),
				'options' => [
					SINGLE_SIGNON_OIDC2 => SingleSignOnProviderHelper::getType(SINGLE_SIGNON_OIDC2),
					//SINGLE_SIGNON_SAML2 => SingleSignOnProviderHelper::getType(SINGLE_SIGNON_SAML2)
				],
				'selected' => $singleSignOnProvider->type
			);

			$uidata->data['server_id'] = array(
				'name' => 'server_id',
				'id' => 'server_id',
				'type' => 'dropdown',
				'class' => 'form-select',
				'value' => set_value('server_id'),
				'options' => $serversList,
				'selected' => $singleSignOnProvider->server_id
			);

			$uidata->data['port'] = array(
				'name' => 'port',
				'id' => 'port',
				'type' => 'text',
				'class' => 'form-control',
				'value' => set_value('port', $singleSignOnProvider->port),
			);

			$uidata->data['credential_id'] = array(
				'name' => 'credential_id',
				'id' => 'credential_id',
				'type' => 'dropdown',
				'class' => 'form-select',
				'value' => set_value('credential_id'),
				'options' => $credentialsList,
				'selected' => $singleSignOnProvider->credential_id
			);

			$uidata->data['proxy_server_id'] = array(
				'name' => 'proxy_server_id',
				'id' => 'proxy_server_id',
				'type' => 'dropdown',
				'class' => 'form-select',
				'value' =>set_value('proxy_server_id'),
				'options' => $proxyServerList,
				'selected' => $singleSignOnProvider->proxy_server_id
			);

			$uidata->data['user_login'] = array(
				'name' => 'user_login[]',
				'id' => 'user_login',
				'type' => 'checkbox',
				'class' => 'form-check-input',
				'checked' => $singleSignOnProvider->user_login,
				'value' =>is_array($user_login_val = set_value('user_login')) ? $user_login_val[0] : set_value('user_login'),
			);

			$uidata->data['query'] = array(
				'name' => 'query[]',
				'id' => 'query',
				'type' => 'checkbox',
				'class' => 'form-check-input',
				'checked' => $singleSignOnProvider->query,
				'value' =>is_array($query_val = set_value('query')) ? $query_val[0] : set_value('query'),
			);

			$uidata->data['authentication_policy'] = array(
				'name' => 'authentication_policy',
				'id' => 'authentication_policy',
				'class' => 'form-select',
				'type' => 'dropdown',
				'value' =>set_value('authentication_policy'),
				'options' => [
					SINGLE_SIGNON_POST_AUTH_CREATE_ACCOUNT => SingleSignOnProviderHelper::getPostAuthenticanPolicy(SINGLE_SIGNON_POST_AUTH_CREATE_ACCOUNT),
					SINGLE_SIGNON_POST_AUTH_LINK_ACCOUNT => SingleSignOnProviderHelper::getPostAuthenticanPolicy(SINGLE_SIGNON_POST_AUTH_LINK_ACCOUNT)
				],
				'selected' => $singleSignOnProvider->authentication_policy
			);
		}

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Update', $data);
	}

	public function Details(int $id)
	{
		$singleSignOnProvider = $this->dbAdapter->Read($id);

		if ($singleSignOnProvider->isNull())
		{
			$this->setStatusMessage("Single Sign-on Provider was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$uidata = new UIData();
		$uidata->title = 'Single Sign-on Provider Details';
		$uidata->data['singleSignOnProvider'] = $singleSignOnProvider;

		$server = (new ServerAdapterFactory())->GetInstance()->Read($singleSignOnProvider->server_id);
		$uidata->data['server'] = $server;

		$credential =
			$singleSignOnProvider->credential_id ?
				(new CredentialAdapterFactory())->GetInstance()->Read($singleSignOnProvider->credential_id) :
				(new EntityFactory())->GetInstance(null);
		$uidata->data['credential'] = $credential;

		$proxyServer = $singleSignOnProvider->proxy_server_id ?
			(new ProxyServerAdapterFactory())->GetInstance()->Read($singleSignOnProvider->proxy_server_id) :
			(new EntityFactory())->GetInstance(null);
		$uidata->data['proxyServer'] = $proxyServer;

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Details', $data);
	}

	public function Delete(int $id)
	{
		$singleSignOnProvider = $this->dbAdapter->Read($id);

		if ($singleSignOnProvider->isNull())
		{
			$this->setStatusMessage("Single Sign-on Provider was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$uidata = new UIData();
		$uidata->title = 'Delete Single Sign-on Provider';
		$uidata->data['singleSignOnProvider'] = $singleSignOnProvider;

		$this->validation->setRules([
			'confirm' => [
				'label'  => 'confirmation',
				'rules'  => 'required',
				'errors' => [
					'required' => '{field} is required.'
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
					$this->setStatusMessage("Single sign-on provider was deleted.", STATUS_SUCCESS);
				}
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem deleting single sign-on provider: " . $ex->getMessage(), STATUS_ERROR);
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
