<?php namespace App\Controllers;

/**
 * ProxyServer.php
 * Created 12/05/2022
 *
 * This class offers CRUD operation for ProxyServer.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Factory\ProxyServerAdapterFactory;
use App\Libraries\CafeVariome\Factory\CredentialAdapterFactory;
use App\Libraries\CafeVariome\Factory\ProxyServerFactory;
use App\Libraries\CafeVariome\Factory\ServerAdapterFactory;
use App\Models\UIData;
use CodeIgniter\Config\Services;

class ProxyServer extends CVUI_Controller
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
		$this->dbAdapter = (new ProxyServerAdapterFactory())->getInstance();

	}

	public function Index()
	{
		return redirect()->to(base_url($this->controllerName . '/List'));
	}

	public function List()
	{
		$uidata = new UIData();
		$uidata->title = 'Proxy Servers';

		$proxyServers = $this->dbAdapter->ReadAll();
		$uidata->data['proxyServers'] = $proxyServers;
		$uidata->css = [VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css'];
		$uidata->javascript = [
			JS. 'cafevariome/proxyserver.js',
			VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js'
		];

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/List', $data);
	}

	public function Create()
	{
		$uidata = new UIData();
		$uidata->title = 'Create Proxy Server';

		$serverAdapterFactory = new ServerAdapterFactory();
		$servers = $serverAdapterFactory->getInstance()->ReadAll();
		$serversList = [-1 => 'Please select a server...'];

		foreach ($servers as $server)
		{
			$serversList[$server->getID()] = $server->name . ' [' . $server->address . ']';
		}

		$credentialAdapterFactory = new CredentialAdapterFactory();
		$credentials = $credentialAdapterFactory->getInstance()->ReadAll();
		$credentialsList = [-1 => 'Please select a credential...'];

		foreach ($credentials as $credential)
		{
			$credentialsList[$credential->getID()] = $credential->name . ($credential->hide_username ? ' [Username is hidden]' : ' [' . $credential->username . ']');
		}

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
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			$name = $this->request->getVar('name');
			$port = $this->request->getVar('port');
			$server_id = $this->request->getVar('server_id');
			$credential_id = $this->request->getVar('credential_id') > 0 ? $this->request->getVar('credential_id') : null;

			try
			{
				$this->dbAdapter->Create((new ProxyServerFactory())->getInstanceFromParameters($name, $port, $server_id, $credential_id));
				$this->setStatusMessage("Proxy server '$name' was created.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem creating proxy server: " . $ex->getMessage(), STATUS_ERROR);
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
				'value' => set_value('name'),
			);

			$uidata->data['server_id'] = array(
				'name' => 'server_id',
				'id' => 'server_id',
				'type' => 'dropdown',
				'class' => 'form-control',
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
				'class' => 'form-control',
				'value' => set_value('credential_id'),
				'options' => $credentialsList
			);
		}

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Create', $data);
	}

	public function Update(int $id)
	{
		$proxyServer = $this->dbAdapter->Read($id);

		if ($proxyServer->isNull())
		{
			$this->setStatusMessage("Proxy server was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$uidata = new UIData();
		$uidata->title = 'Edit Proxy Server';
		$uidata->data['id'] = $proxyServer->getID();

		$serverAdapterFactory = new ServerAdapterFactory();
		$servers = $serverAdapterFactory->getInstance()->ReadAll();
		$serversList = [-1 => 'Please select a server...'];

		foreach ($servers as $server)
		{
			$serversList[$server->getID()] = $server->name . ' [' . $server->address . ']';
		}

		$credentialAdapterFactory = new CredentialAdapterFactory();
		$credentials = $credentialAdapterFactory->getInstance()->ReadAll();
		$credentialsList = [-1 => 'Please select a credential...'];

		foreach ($credentials as $credential)
		{
			$credentialsList[$credential->getID()] = $credential->name . ($credential->hide_username ? ' [Username is hidden]' : ' [' . $credential->username . ']');
		}

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
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			$name = $this->request->getVar('name');
			$port = $this->request->getVar('port');
			$server_id = $this->request->getVar('server_id');
			$credential_id = $this->request->getVar('credential_id') > 0 ? $this->request->getVar('credential_id') : null;

			try
			{
				$this->dbAdapter->Update($id, (new ProxyServerFactory())->getInstanceFromParameters($name, $port, $server_id, $credential_id));
				$this->setStatusMessage("Proxy server '' was updated.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem updating proxy server: " . $ex->getMessage(), STATUS_ERROR);
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
				'value' => set_value('name', $proxyServer->name),
			);

			$uidata->data['server_id'] = array(
				'name' => 'server_id',
				'id' => 'server_id',
				'type' => 'dropdown',
				'class' => 'form-control',
				'value' => set_value('server_id', $proxyServer->server_id),
				'options' => $serversList,
				'selected' => $proxyServer->server_id
			);

			$uidata->data['port'] = array(
				'name' => 'port',
				'id' => 'port',
				'type' => 'text',
				'class' => 'form-control',
				'value' => set_value('port', $proxyServer->port),
			);

			$uidata->data['credential_id'] = array(
				'name' => 'credential_id',
				'id' => 'credential_id',
				'type' => 'dropdown',
				'class' => 'form-control',
				'value' => set_value('credential_id', $proxyServer->credential_id),
				'options' => $credentialsList,
				'selected' => $proxyServer->credential_id
			);
		}

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Update', $data);
	}

	public function Details(int $id)
	{
		$proxyServer = $this->dbAdapter->Read($id);

		if ($proxyServer->isNull())
		{
			$this->setStatusMessage("Proxy server was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$uidata = new UIData();
		$uidata->title = 'Proxy Server Details';
		$uidata->data['proxyServer'] = $proxyServer;
		$uidata->data['server'] = (new ServerAdapterFactory())->getInstance()->Read($proxyServer->server_id);
		$uidata->data['credential'] = $proxyServer->credential_id != null ? (new CredentialAdapterFactory())->getInstance()->Read($proxyServer->credential_id) : null;

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Details', $data);
	}

	public function Delete(int $id)
	{
		$uidata = new UIData();
		$uidata->title = '';

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			try
			{
				$this->setStatusMessage("Proxy server '' was deleted.", STATUS_SUCCESS);

			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem deleting Proxy server" . $ex->getMessage(), STATUS_ERROR);
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
