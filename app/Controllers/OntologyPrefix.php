<?php namespace App\Controllers;

use App\Libraries\CafeVariome\Entities\ViewModels\OntologyDropDown;
use App\Libraries\CafeVariome\Entities\ViewModels\OntologyPrefixWithOntologyName;
use App\Libraries\CafeVariome\Factory\OntologyAdapterFactory;
use App\Libraries\CafeVariome\Factory\OntologyPrefixAdapterFactory;
use App\Libraries\CafeVariome\Factory\OntologyPrefixFactory;
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
	private $validation;

	private $ontologyAdapter;

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
		$this->dbAdapter = (new OntologyPrefixAdapterFactory())->GetInstance();
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
		$uidata->title = 'Ontology Prefixes';

		$uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
		$uidata->javascript = array(JS.'cafevariome/ontologyprefix.js', VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js');

		$uidata->data['prefixes'] = $this->dbAdapter->ReadByOntologyId($ontology_id);
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
		$uidata->title = 'Create Ontology Prefix';
		$uidata->data['ontology'] = $ontology;

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
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
			try {
				$name = $this->request->getVar('name');
				$ontology_id = $this->request->getVar('ontology_id');
				$this->dbAdapter->Create(
					(new OntologyPrefixFactory())->GetInstanceFromParameters($name, $ontology_id)
				);

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
		$ontologyPrefix = $this->dbAdapter->SetModel(OntologyPrefixWithOntologyName::class)->Read($id);
		if ($ontologyPrefix->isNull())
		{
			return redirect()->to(base_url('Ontology'));
		}

		$uidata = new UIData();
		$uidata->title = 'Update Ontology Prefix';
		$uidata->data['ontologyPrefix'] = $ontologyPrefix;
		$ontology_id = $ontologyPrefix->ontology_id;
		$this->validation->setRules([
			'name' => [
				'label'  => 'Name',
				'rules'  => 'required|alpha_numeric_punct|unique_ontology_prefix[ontology_id, ontology_prefix_id]|max_length[50]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_numeric_punct' => 'The only valid characters for {field} are alphabetical characters, numbers, and some punctuation characters.',
					'unique_ontology_prefix' => '{field} already exists.',
					'max_length' => 'Maximum length is 50 characters.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			try
			{
				$name = $this->request->getVar('name');
				$this->dbAdapter->Update(
					$id, (new OntologyPrefixFactory())->GetInstanceFromParameters($name, $ontology_id)
				);

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
				'value' =>set_value('name', $ontologyPrefix->name)
			);
		}

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Update', $data);
	}

	public function Delete(int $id)
	{
		$ontologyPrefix = $this->dbAdapter->Read($id);
		if ($ontologyPrefix->isNull())
		{
			return redirect()->to(base_url('Ontology'));
		}

		$uidata = new UIData();
		$uidata->title = "Delete Ontology Prefix";
		$uidata->data['ontologyPrefix'] = $ontologyPrefix;
		$ontology_id = $ontologyPrefix->ontology_id;

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
					$ontologyPrefixName = $ontologyPrefix->name;
					$this->dbAdapter->Delete($id);
					$this->setStatusMessage("Ontology prefix '$ontologyPrefixName' was deleted.", STATUS_SUCCESS);

				}
				catch (\Exception $ex)
				{
					$this->setStatusMessage("There was a problem deleting the ontology prefix.", STATUS_ERROR);
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
