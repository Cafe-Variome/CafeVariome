<?php namespace App\Controllers;

use App\Libraries\CafeVariome\Factory\OntologyAdapterFactory;
use App\Libraries\CafeVariome\Factory\OntologyFactory;
use App\Models\UIData;
use CodeIgniter\Config\Services;

/**
 * Ontology.php
 * Created 28/09/2021
 *
 * This class offers CRUD operation for ontologies.
 * @author Mehdi Mehtarizadeh
 */

class Ontology extends CVUI_Controller
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
		$this->dbAdapter = (new OntologyAdapterFactory())->GetInstance();
	}

	public function Index()
	{
		return redirect()->to(base_url($this->controllerName . '/List'));
	}

	public function Create()
	{
		$uidata = new UIData();
		$uidata->title = 'Create Ontology';

		$this->validation->setRules([
			'name' => [
				'label'  => 'Name',
				'rules'  => 'required|alpha_numeric_space|is_unique[ontologies.name]|max_length[128]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_numeric_space' => 'The only valid characters for {field} are alphabetical characters, numbers, and spaces.',
					'is_unique' => '{field} already exists.',
					'max_length' => 'Maximum length for {field} is 128 characters.'
				]
			],
			'desc' => [
				'label'  => 'Description',
				'rules'  => 'text_validator|max_length[65535]',
				'errors' => [
					'max_length' => 'Maximum length for {field} is 65,535 characters.'
				]
			],
			'node_key' => [
				'label'  => 'Node Key',
				'rules'  => 'required|alpha_dash|max_length[128]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_dash' => 'The only valid characters for {field} are alphabetical characters, underscores, dashes, and numbers.',
					'max_length' => 'Maximum length is 128 characters.'
				]
			],
			'node_type' => [
				'label'  => 'Node Type',
				'rules'  => 'required|alpha_dash|max_length[128]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_dash' => 'The only valid characters for {field} are alphabetical characters, underscores, dashes, and numbers.',
					'max_length' => 'Maximum length is 128 characters.'
				]
			],
			'key_prefix' => [
				'label'  => 'Key Prefix',
				'rules'  => 'permit_empty|alpha_numeric_punct|max_length[128]',
				'errors' => [
					'alpha_numeric_punct' => 'The only valid characters for {field} are alphabetical characters, numbers, punctuation characters, and spaces.',
					'max_length' => 'Maximum length is 128 characters.'
				]
			],
			'term_name' => [
				'label'  => 'Term Name',
				'rules'  => 'permit_empty|alpha_dash|max_length[128]',
				'errors' => [
					'alpha_dash' => 'The only valid characters for {field} are alphabetical characters, underscores, dashes, and numbers.',
					'max_length' => 'Maximum length is 128 characters.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			try
			{
				$name = $this->request->getVar('name');
				$description = $this->request->getVar('desc');
				$node_key = $this->request->getVar('node_key');
				$node_type = $this->request->getVar('node_type');
				$key_prefix = $this->request->getVar('key_prefix');
				$term_name = $this->request->getVar('term_name');

				$this->dbAdapter->Create((new OntologyFactory())->GetInstanceFromParameters($name, $description, $node_key, $node_type, $key_prefix, $term_name));

				$this->setStatusMessage("Ontology '$name' was created.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem creating ontology: "  . $ex->getMessage(), STATUS_ERROR);
			}

			return redirect()->to(base_url($this->controllerName.'/List'));
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

			$uidata->data['desc'] = array(
				'name' => 'desc',
				'id' => 'desc',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('desc'),
			);

			$uidata->data['node_key'] = array(
				'name' => 'node_key',
				'id' => 'node_key',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('node_key'),
			);

			$uidata->data['node_type'] = array(
				'name' => 'node_type',
				'id' => 'node_type',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('node_type'),
			);

			$uidata->data['key_prefix'] = array(
				'name' => 'key_prefix',
				'id' => 'key_prefix',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('key_prefix'),
			);

			$uidata->data['term_name'] = array(
				'name' => 'term_name',
				'id' => 'term_name',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('term_name'),
			);

		}

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Create', $data);
	}

	public function List()
	{
		$uidata = new UIData();
		$uidata->title = 'Ontologies';

		$uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
		$uidata->javascript = array(JS.'cafevariome/ontology.js', VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js');


		$uidata->data['ontologies'] = $this->dbAdapter->ReadAll();

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/List', $data);
	}

	public function Update(int $id)
	{
		$ontology = $this->dbAdapter->Read($id);

		if ($ontology->isNull())
		{
			$this->setStatusMessage("Ontology was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$uidata = new UIData();
		$uidata->title = 'Edit Ontology';

		$this->validation->setRules([
			'name' => [
				'label'  => 'Name',
				'rules'  => 'required|alpha_numeric_space|is_unique[ontologies.name,id,{id}]|max_length[50]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_numeric_space' => 'The only valid characters for {field} are alphabetical characters, numbers, and spaces.',
					'is_unique' => '{field} already exists.',
					'max_length' => 'Maximum length is 50 characters.'
				]
			],
			'desc' => [
				'label'  => 'Description',
				'rules'  => 'text_validator|max_length[65535]',
				'errors' => [
					'max_length' => 'Maximum length for {field} is 65,535 characters.'
				]
			],
			'node_key' => [
				'label'  => 'Node Key',
				'rules'  => 'required|alpha_dash|max_length[128]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_dash' => 'The only valid characters for {field} are alphabetical characters, underscores, dashes, and numbers.',
					'max_length' => 'Maximum length is 128 characters.'
				]
			],
			'node_type' => [
				'label'  => 'Node Type',
				'rules'  => 'required|alpha_dash|max_length[128]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_dash' => 'The only valid characters for {field} are alphabetical characters, underscores, dashes, and numbers.',
					'max_length' => 'Maximum length is 128 characters.'
				]
			],
			'key_prefix' => [
				'label'  => 'Key Prefix',
				'rules'  => 'permit_empty|alpha_numeric_punct|max_length[128]',
				'errors' => [
					'alpha_numeric_punct' => 'The only valid characters for {field} are alphabetical characters, numbers, punctuation characters, and spaces.',
					'max_length' => 'Maximum length is 128 characters.'
				]
			],
			'term_name' => [
				'label'  => 'Term Name',
				'rules'  => 'permit_empty|alpha_dash|max_length[128]',
				'errors' => [
					'alpha_dash' => 'The only valid characters for {field} are alphabetical characters, underscores, dashes, and numbers.',
					'max_length' => 'Maximum length is 128 characters.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			try
			{
				$name = $this->request->getVar('name');
				$description = $this->request->getVar('desc');
				$node_key = $this->request->getVar('node_key');
				$node_type = $this->request->getVar('node_type');
				$key_prefix = $this->request->getVar('key_prefix');
				$term_name = $this->request->getVar('term_name');

				$this->dbAdapter->Update($id, (new OntologyFactory())->GetInstanceFromParameters(
					$name, $description, $node_key, $node_type, $key_prefix, $term_name
				));
				$this->setStatusMessage("Ontology '$name' was updated.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem updating $name: "  . $ex->getMessage(), STATUS_ERROR);
			}

			return redirect()->to(base_url($this->controllerName.'/List'));
		}
		else
		{
			$uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

			$uidata->data['id'] = $ontology->getID();

			$uidata->data['name'] = array(
				'name' => 'name',
				'id' => 'name',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('name', $ontology->name),
			);

			$uidata->data['desc'] = array(
				'name' => 'desc',
				'id' => 'desc',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('desc', $ontology->description),
			);

			$uidata->data['node_key'] = array(
				'name' => 'node_key',
				'id' => 'node_key',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('node_key', $ontology->node_key),
			);

			$uidata->data['node_type'] = array(
				'name' => 'node_type',
				'id' => 'node_type',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('node_type', $ontology->node_type),
			);

			$uidata->data['key_prefix'] = array(
				'name' => 'key_prefix',
				'id' => 'key_prefix',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('key_prefix', $ontology->key_prefix),
			);

			$uidata->data['term_name'] = array(
				'name' => 'term_name',
				'id' => 'term_name',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('term_name', $ontology->term_name),
			);

		}

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Update', $data);
	}

	public function Delete(int $id)
	{
		$ontology = $this->dbAdapter->Read($id);

		if ($ontology->isNull())
		{
			$this->setStatusMessage("Ontology was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$uidata = new UIData();
		$uidata->title = "Delete Ontology";

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
			$confirm = $this->request->getVar('confirm');
			if ($confirm == 'yes')
			{
				try
				{
					$this->dbAdapter->Delete($id);
					$this->setStatusMessage("Ontology was deleted.", STATUS_SUCCESS);
				}
				catch (\Exception $ex)
				{
					$this->setStatusMessage("There was a problem deleting the ontology.", STATUS_ERROR);
				}
			}
			return redirect()->to(base_url($this->controllerName.'/List'));
		}
		else
		{
			$uidata->data['ontology'] = $ontology;
		}

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory.'/Delete', $data);
	}

	public function Details(int $id)
	{
		$ontology = $this->dbAdapter->Read($id);

		if ($ontology->isNull())
		{
			$this->setStatusMessage("Ontology was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$uidata = new UIData();
		$uidata->title = 'Ontology Details';
		$uidata->data['ontology'] = $ontology;

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Details', $data);
	}
}
