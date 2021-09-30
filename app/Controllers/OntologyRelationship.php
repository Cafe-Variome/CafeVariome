<?php namespace App\Controllers;

use App\Models\UIData;
use CodeIgniter\Config\Services;

/**
 * OntologyRelationship.php
 * Created 29/09/2021
 *
 * This class offers CRUD operation for OntologyRelationships.
 * @author Mehdi Mehtarizadeh
 */

class OntologyRelationship extends CVUI_Controller
{

	private $ontologyModel;
	private $relationshipModel;
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
		$this->relationshipModel = new \App\Models\OntologyRelationship();
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
		$uidata->title = 'Ontology Relationships';


		$uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
		$uidata->javascript = array(JS.'cafevariome/ontologyrelationship.js', VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js');

		$uidata->data['relationships'] = $this->relationshipModel->getOntologyRelationships($ontology_id);
		$uidata->data['ontology_name'] = $ontology_name;
		$uidata->data['ontology_id'] = $ontology_id;

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/List', $data);
	}

	public function Create(int $ontology_id)
	{
		$ontology_name = $this->ontologyModel->getOntologyNameById($ontology_id);

		$uidata = new UIData();
		$uidata->title = 'Create Ontology Relationship';
		$uidata->data['ontology_name'] = $ontology_name;
		$uidata->data['ontology_id'] = $ontology_id;

		$this->validation->setRules([
			'name' => [
				'label'  => 'Name',
				'rules'  => 'required|alpha_numeric_punct|unique_ontology_relationship[ontology_id]|max_length[100]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_numeric_punct' => 'The only valid characters for {field} are alphabetical characters, numbers, and some punctuation characters.',
					'unique_ontology_relationship' => '{field} already exists.',
					'max_length' => 'Maximum length is 100 characters.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
			try {
				$name = $this->request->getVar('name');
				$ontology_id = $this->request->getVar('ontology_id');
				$this->relationshipModel->createOntologyRelationship($name, $ontology_id);

				$this->setStatusMessage("Ontology relationship '$name' was created.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem creating ontology relationship" . $ex->getMessage(), STATUS_ERROR);
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
				'value' =>set_value('name')
			);
		}

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Create', $data);
	}

	public function Update(int $id)
	{
		$ontologyRelationship = $this->relationshipModel->getOntologyRelationship($id);
		if ($ontologyRelationship == null || $id <= 0){
			return redirect()->to(base_url('Ontology'));
		}

		$uidata = new UIData();
		$uidata->title = 'Update Ontology Relationship';
		$uidata->data['relationship_id'] = $id;

		$ontology = $this->ontologyModel->getOntologyNameById($ontologyRelationship['ontology_id']);
		$uidata->data['ontology_name'] = $ontology;
		$ontology_id = $ontologyRelationship['ontology_id'];
		$uidata->data['ontology_id'] = $ontology_id;

		$this->validation->setRules([
			'name' => [
				'label'  => 'Name',
				'rules'  => 'required|alpha_numeric_punct|unique_ontology_relationship[ontology_id, relationship_id]|max_length[100]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_numeric_punct' => 'The only valid characters for {field} are alphabetical characters, numbers, and some punctuation characters.',
					'unique_ontology_relationship' => '{field} already exists.',
					'max_length' => 'Maximum length is 100 characters.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
			try {
				$name = $this->request->getVar('name');
				$id = $this->request->getVar('relationship_id');
				$this->relationshipModel->updateOntologyRelationship($id, $name);

				$this->setStatusMessage("Ontology relationship '$name' was updated.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem updating ontology relationship" . $ex->getMessage(), STATUS_ERROR);
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
				'value' =>set_value('name', $ontologyRelationship['name']),
			);
		}

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Update', $data);
	}

	public function Delete(int $id)
	{
		$ontologyRelationship = $this->relationshipModel->getOntologyRelationship($id);
		if ($ontologyRelationship == null || $id <= 0){
			return redirect()->to(base_url('Ontology'));
		}

		$uidata = new UIData();
		$uidata->title = "Delete Ontology Relationship";
		$uidata->data['relationship_id'] = $id;
		$uidata->data['ontology_relationship_name'] = $ontologyRelationship['name'];
		$ontology_id = $ontologyRelationship['ontology_id'];
		$uidata->data['ontology_id'] = $ontology_id;

		$this->validation->setRules([
			'confirm' => [
				'label'  => 'confirmation',
				'rules'  => 'required',
				'errors' => [
					'required' => '{field} is required.'
				]
			],

			'ontology_relationship_id' => [
				'label'  => 'Ontology Relationship Id',
				'rules'  => 'required|alpha_dash',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_dash' => '{field} must only contain alpha-numeric characters, underscores, or dashes.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
			$id = $this->request->getVar('ontology_relationship_id');
			$confirm = $this->request->getVar('confirm');
			if ($confirm == 'yes') {
				try {
					$ontologyRelationship = $this->relationshipModel->getOntologyRelationship($id);
					if ($ontologyRelationship)  {
						$ontologyRelationshipName = $ontologyRelationship['name'];
						$this->relationshipModel->deleteOntologyRelationship($id);
						$this->setStatusMessage("Ontology relationship '$ontologyRelationshipName' was deleted.", STATUS_SUCCESS);
					}
					else{
						$this->setStatusMessage("Ontology relationship does not exist.", STATUS_ERROR);
					}
				} catch (\Exception $ex) {
					$this->setStatusMessage("There was a problem deleting the ontology relationship.", STATUS_ERROR);
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
