<?php namespace App\Controllers;

use App\Libraries\CafeVariome\Database\ServerAdapter;
use App\Libraries\CafeVariome\Factory\EntityFactory;
use App\Libraries\CafeVariome\Factory\ServerAdapterFactory;
use App\Libraries\CafeVariome\Factory\ServerFactory;
use App\Models\UIData;
use CodeIgniter\Config\Services;

/**
 * Server.php
 * Created 22/04/2022
 *
 * This class offers CRUD operation for Server.
 * @author Mehdi Mehtarizadeh
 */


class Server extends CVUI_Controller
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
		$this->dbAdapter = (new ServerAdapterFactory())->getInstance();

	}

	public function Index()
	{
		return redirect()->to(base_url('Server'));
	}

	public function List()
	{
		$uidata = new UIData();
		$uidata->title = 'Servers';

		$serverAdapter = new ServerAdapter();
		$servers = $serverAdapter->ReadAll();

		$uidata->data['servers'] = $servers;
		$uidata->css = [VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css'];
		$uidata->javascript = [
			JS. 'cafevariome/server.js',
			VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js'
		];

		$data = $this->wrapData($uidata);
		return view($this->controllerName . '/List', $data);
	}

	public function Create()
	{
		$uidata = new UIData();
		$uidata->title = 'Create Server';

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
			'address' => [
				'label'  => 'Address',
				'rules'  => 'required|valid_url_strict|max_length[512]',
				'errors' => [
					'required' => '{field} is required.',
					'valid_url_strict' => '{field} must be a valid URL.',
					'max_length' => 'Maximum length is 512 characters.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			try
			{
				$name = $this->request->getVar('name');
				$address = $this->request->getVar('address');

				$this->dbAdapter->Create((new ServerFactory())->getInstanceFromParameters($name, $address));

				$this->setStatusMessage("Server '$name' was created.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem creating Server" . $ex->getMessage(), STATUS_ERROR);
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

			$uidata->data['address'] = array(
				'name' => 'address',
				'id' => 'address',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('address'),
			);
		}

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Create', $data);
	}

	public function Update(int $id)
	{
		$server = $this->dbAdapter->Read($id);

		if ($server->isNull())
		{
			$this->setStatusMessage("Server was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$uidata = new UIData();
		$uidata->title = 'Edit Server';
		$uidata->data['id'] = $server->getID();

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
			'address' => [
				'label'  => 'Address',
				'rules'  => 'required|valid_url_strict|max_length[512]',
				'errors' => [
					'required' => '{field} is required.',
					'valid_url_strict' => '{field} must be a valid URL.',
					'max_length' => 'Maximum length is 512 characters.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			try
			{
				$name = $this->request->getVar('name');
				$address = $this->request->getVar('address');

				$this->dbAdapter->Update($id, (new ServerFactory())->getInstanceFromParameters($name, $address, $server->removable));

				$this->setStatusMessage("Server '$name' was updated.", STATUS_SUCCESS);

			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem updating Server" . $ex->getMessage(), STATUS_ERROR);
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
				'value' =>set_value('name', $server->name),
			);

			$uidata->data['address'] = array(
				'name' => 'address',
				'id' => 'address',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('address', $server->address),
			);
		}

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Update', $data);
	}

	public function Details(int $id)
	{
		$uidata = new UIData();
		$uidata->title = '';

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Details', $data);
	}

	public function Delete(int $id)
	{
		$server = $this->dbAdapter->Read($id);

		if ($server->isNull())
		{
			$this->setStatusMessage("Server was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$uidata = new UIData();
		$uidata->title = 'Delete Server';
		$uidata->data['server'] = $server;

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
					$this->setStatusMessage("Server was deleted.", STATUS_SUCCESS);
				}
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem deleting Server" . $ex->getMessage(), STATUS_ERROR);
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
