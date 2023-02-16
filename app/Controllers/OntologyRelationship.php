<?php namespace App\Controllers;

/**
 * OntologyRelationship.php
 * Created 29/09/2021
 *
 * This class offers CRUD operation for OntologyRelationships.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\ViewModels\OntologyDropDown;
use App\Libraries\CafeVariome\Entities\ViewModels\OntologyRelationshipWithOntologyName;
use App\Libraries\CafeVariome\Factory\OntologyAdapterFactory;
use App\Libraries\CafeVariome\Factory\OntologyRelationshipAdapterFactory;
use App\Libraries\CafeVariome\Factory\OntologyRelationshipFactory;
use App\Models\UIData;
use CodeIgniter\Config\Services;

class OntologyRelationship extends CVUI_Controller
{

	private $ontologyAdapter;
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
		$this->dbAdapter = (new OntologyRelationshipAdapterFactory())->GetInstance();
		$this->ontologyAdapter = (new OntologyAdapterFactory())->GetInstance();
	}

	public function Index()
	{
		return redirect()->to(base_url('Ontology'));
	}

	public function List(int $ontology_id)
	{
		$ontology = $this->ontologyAdapter->SetModel(OntologyDropDown::class)->Read($ontology_id);
		if ($ontology->isNull())
		{
			return redirect()->to(base_url('Ontology'));
		}

		$uidata = new UIData();
		$uidata->title = 'Ontology Relationships';

		$uidata->IncludeJavaScript(JS.'cafevariome/ontologyrelationship.js');
		$uidata->IncludeDataTables();

		$uidata->data['relationships'] = $this->dbAdapter->ReadByOntologyId($ontology_id);
		$uidata->data['ontology'] = $ontology;

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/List', $data);
	}

	public function Create(int $ontology_id)
	{
		$ontology = $this->ontologyAdapter->SetModel(OntologyDropDown::class)->Read($ontology_id);
		if ($ontology->isNull())
		{
			return redirect()->to(base_url('Ontology'));
		}
		$uidata = new UIData();
		$uidata->title = 'Create Ontology Relationship';
		$uidata->data['ontology'] = $ontology;

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
			],
			'ontology_id' => [
				'label'  => 'ontology_id',
				'rules'  => 'integer',
				'errors' => [
					'integer' => 'The only valid input for {field} is integers.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			try
			{
				$name = $this->request->getVar('name');
				$ontology_id = $this->request->getVar('ontology_id');
				$this->dbAdapter->Create(
					(new OntologyRelationshipFactory())->GetInstanceFromParameters($name, $ontology_id)
				);

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
		$ontologyRelationship = $this->dbAdapter->SetModel(OntologyRelationshipWithOntologyName::class)->Read($id);
		if ($ontologyRelationship->isNull())
		{
			return redirect()->to(base_url('Ontology'));
		}

		$uidata = new UIData();
		$uidata->title = 'Update Ontology Relationship';
		$uidata->data['ontologyRelationship'] = $ontologyRelationship;
		$ontology_id = $ontologyRelationship->ontology_id;

		$this->validation->setRules([
			'name' => [
				'label'  => 'Name',
				'rules'  => 'required|alpha_numeric_punct|unique_ontology_relationship[ontology_id, ontology_relationship_id]|max_length[100]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_numeric_punct' => 'The only valid characters for {field} are alphabetical characters, numbers, and some punctuation characters.',
					'unique_ontology_relationship' => '{field} already exists.',
					'max_length' => 'Maximum length is 100 characters.'
				]
			],
			'relationship_id' => [
				'label'  => 'relationship_id',
				'rules'  => 'integer',
				'errors' => [
					'integer' => 'The only valid input for {field} is integers.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			try
			{
				$name = $this->request->getVar('name');

				$this->dbAdapter->Update(
					$id, (new OntologyRelationshipFactory())->GetInstanceFromParameters($name, $ontology_id)
				);

				$this->setStatusMessage("Ontology relationship '$name' was updated.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem updating ontology relationship: " . $ex->getMessage(), STATUS_ERROR);
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
				'value' =>set_value('name', $ontologyRelationship->name),
			);
		}

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Update', $data);
	}

	public function Delete(int $id)
	{
		$ontologyRelationship = $this->dbAdapter->SetModel(OntologyRelationshipWithOntologyName::class)->Read($id);
		if ($ontologyRelationship->isNull())
		{
			return redirect()->to(base_url('Ontology'));
		}

		$uidata = new UIData();
		$uidata->title = "Delete Ontology Relationship";
		$uidata->data['ontologyRelationship'] = $ontologyRelationship;
		$ontology_id = $ontologyRelationship->ontology_id;

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
					$ontologyRelationshipName = $ontologyRelationship->name;
					$this->dbAdapter->Delete($id);
					$this->setStatusMessage("Ontology relationship '$ontologyRelationshipName' was deleted.", STATUS_SUCCESS);
				}
				catch (\Exception $ex)
				{
					$this->setStatusMessage("There was a problem deleting the ontology relationship.", STATUS_ERROR);
				}
			}
			return redirect()->to(base_url($this->controllerName.'/List/' . $ontology_id));
		}
		else
		{
			$data = $this->wrapData($uidata);

			return view($this->viewDirectory.'/Delete', $data);
		}
	}
}
