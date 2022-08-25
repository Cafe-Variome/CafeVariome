<?php namespace App\Controllers;

use App\Models\UIData;
use CodeIgniter\Config\Services;

/**
 * OntologyPrefix.php
 * Created 29/09/2021
 *
 * This class offers CRUD operation for OntologyPrefixes.
 * @author Mehdi Mehtarizadeh
 */

class OntologyPrefix extends CVUI_Controller
{
	private $ontologyModel;
	private $prefixModel;
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
		$this->ontologyModel = new \App\Models\Ontology();
		$this->prefixModel = new \App\Models\OntologyPrefix();
	}

	public function Index()
	{
		return redirect()->to(base_url('Ontology'));
	}

	public function List(int $ontology_id)
	{
		$ontology_name = $this->ontologyModel->getOntologyNameById($ontology_id);
		if ($ontology_name == null || $ontology_id <= 0){
			return redirect()->to(base_url('Ontology'));
		}

		$uidata = new UIData();
		$uidata->title = 'Ontology Prefixes';

		$uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
		$uidata->javascript = array(JS.'cafevariome/ontologyprefix.js', VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js');

		$uidata->data['prefixes'] = $this->prefixModel->getOntologyPrefixes($ontology_id);
		$uidata->data['ontology_name'] = $ontology_name;
		$uidata->data['ontology_id'] = $ontology_id;

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/List', $data);
	}

	public function Create(int $ontology_id)
	{
		$ontology_name = $this->ontologyModel->getOntologyNameById($ontology_id);

		$uidata = new UIData();
		$uidata->title = 'Create Ontology Prefix';
		$uidata->data['ontology_name'] = $ontology_name;
		$uidata->data['ontology_id'] = $ontology_id;

		$this->validation->setRules([
			'name' => [
				'label'  => 'Name',
				'rules'  => 'required|alpha_numeric_punct|unique_ontology_prefix[ontology_id]|max_length[50]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_numeric_punct' => 'The only valid characters for {field} are alphabetical characters, numbers, and some punctuation characters.',
					'unique_ontology_prefix' => '{field} already exists.',
					'max_length' => 'Maximum length is 50 characters.'
				]
			]
			//@TODO add check for ontology ID to make sure it's an integer!
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
			try {
				$name = $this->request->getVar('name');
				$ontology_id = $this->request->getVar('ontology_id');
				$this->prefixModel->createOntologyPrefix($name, $ontology_id);

				$this->setStatusMessage("Ontology prefix '$name' was created.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem creating ontology prefix"  . $ex->getMessage(), STATUS_ERROR);
			}

			return redirect()->to(base_url($this->controllerName.'/List/' . $ontology_id));
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
		}

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Create', $data);
	}

	public function Update(int $id)
	{
		$ontologyPrefix = $this->prefixModel->getOntologyPrefix($id);
		if ($ontologyPrefix == null || $id <= 0){
			return redirect()->to(base_url('Ontology'));
		}

		$uidata = new UIData();
		$uidata->title = 'Update Ontology Prefix';
		$uidata->data['prefix_id'] = $id;

		$ontology = $this->ontologyModel->getOntologyNameById($ontologyPrefix['ontology_id']);
		$uidata->data['ontology_name'] = $ontology;
		$ontology_id = $ontologyPrefix['ontology_id'];
		$uidata->data['ontology_id'] = $ontology_id;

		$this->validation->setRules([
			'name' => [
				'label'  => 'Name',
				'rules'  => 'required|alpha_numeric_punct|unique_ontology_prefix[ontology_id, prefix_id]|max_length[50]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_numeric_punct' => 'The only valid characters for {field} are alphabetical characters, numbers, and some punctuation characters.',
					'unique_ontology_prefix' => '{field} already exists.',
					'max_length' => 'Maximum length is 50 characters.'
				]
			]
			//@TODO add check for ontology ID to make sure it's an integer AND REQUIRED!
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
			try {
				$name = $this->request->getVar('name');
				$id = $this->request->getVar('prefix_id');
				$this->prefixModel->updateOntologyPrefix($id, $name);

				$this->setStatusMessage("Ontology prefix '$name' was updated.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem updating ontology prefix"  . $ex->getMessage(), STATUS_ERROR);
			}

			return redirect()->to(base_url($this->controllerName.'/List/' . $ontology_id));
		}
		else
		{
			$uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

			$uidata->data['name'] = array(
				'name' => 'name',
				'id' => 'name',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('name', $ontologyPrefix['name'])
			);
		}

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Update', $data);
	}

	public function Delete(int $id)
	{
		$ontologyPrefix = $this->prefixModel->getOntologyPrefix($id);
		if($ontologyPrefix == null || $id <= 0){
			return redirect()->to(base_url('Ontology'));
		}

		$uidata = new UIData();
		$uidata->title = "Delete Ontology Prefix";
		$uidata->data['prefix_id'] = $id;
		$uidata->data['ontology_prefix_name'] = $ontologyPrefix['name'];
		$ontology_id = $ontologyPrefix['ontology_id'];
		$uidata->data['ontology_id'] = $ontology_id;

		$this->validation->setRules([
			'confirm' => [
				'label'  => 'confirmation',
				'rules'  => 'required',
				'errors' => [
					'required' => '{field} is required.'
				]
			],

			'ontology_prefix_id' => [
				'label'  => 'Ontology Prefix Id',
				'rules'  => 'required|alpha_dash',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_dash' => '{field} must only contain alpha-numeric characters, underscores, or dashes.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
			$id = $this->request->getVar('ontology_prefix_id');
			$confirm = $this->request->getVar('confirm');
			if ($confirm == 'yes') {
				try {
					$ontologyPrefix = $this->prefixModel->getOntologyPrefix($id);
					if ($ontologyPrefix)  {
						$ontologyPrefixName = $ontologyPrefix['name'];
						$this->prefixModel->deleteOntologyPrefix($id);
						$this->setStatusMessage("Ontology prefix '$ontologyPrefixName' was deleted.", STATUS_SUCCESS);
					}
					else{
						$this->setStatusMessage("Ontology prefix does not exist.", STATUS_ERROR);
					}
				} catch (\Exception $ex) {
					$this->setStatusMessage("There was a problem deleting the ontology prefix.", STATUS_ERROR);
				}
			}
			return redirect()->to(base_url($this->controllerName.'/List/' . $ontology_id));
		}
		else {
			$data = $this->wrapData($uidata);

			return view($this->viewDirectory.'/Delete', $data);
		}
	}

}
