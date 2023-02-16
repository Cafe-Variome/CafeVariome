<?php namespace App\Controllers;

/**
 * Attribute.php
 * Created 15/09/2021
 *
 * This class offers CRUD operation for data attributes.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Core\DataPipeLine\DataPipeLine;
use App\Libraries\CafeVariome\Database\SourceAdapter;
use App\Libraries\CafeVariome\Entities\ViewModels\AttributeAssociation;
use App\Libraries\CafeVariome\Entities\ViewModels\AttributeDetails;
use App\Libraries\CafeVariome\Entities\ViewModels\AttributeList;
use App\Libraries\CafeVariome\Entities\ViewModels\OntologyDropDown;
use App\Libraries\CafeVariome\Factory\AttributeAdapterFactory;
use App\Libraries\CafeVariome\Factory\AttributeFactory;
use App\Libraries\CafeVariome\Factory\OntologyAdapterFactory;
use App\Libraries\CafeVariome\Factory\SourceAdapterFactory;
use App\Models\UIData;
use CodeIgniter\Config\Services;

class Attribute extends CVUI_Controller
{
	private $validation;

	private SourceAdapter $sourceAdapter;

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
		$this->sourceAdapter = (new SourceAdapterFactory())->GetInstance();
		$this->dbAdapter = (new AttributeAdapterFactory())->GetInstance();
	}

	public function Index()
	{
		return redirect()->to(base_url('Source'));
	}

	public function List(int $source_id)
	{
		$source = $this->sourceAdapter->Read($source_id);

		if ($source->isNull())
		{
			$this->setStatusMessage("Source was not found.", STATUS_ERROR);
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Attributes';

		$attributes = $this->dbAdapter->SetModel(AttributeList::class)->ReadBySourceId($source_id);

		$uidata->data['source_id'] = $source->getID();
		$uidata->data['source_name'] = $source->name;
		$uidata->data['attributes'] = $attributes;

		$uidata->IncludeJavaScript(JS . 'cafevariome/attribute.js');
		$uidata->IncludeDataTables();

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory . '/List', $data);
	}

	public function Update(int $id)
	{
		$attribute = $this->dbAdapter->Read($id);
		if ($attribute->isNull())
		{
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Edit Attribute';

		$uidata->data['id'] = $attribute->getID();
		$source_id = $attribute->source_id;
		$uidata->data['source_id'] = $source_id;

		$this->validation->setRules([
			'display_name' => [
				'label' => 'Display Name',
				'rules' => 'required|alpha_numeric_space',
				'errors' => [
					'required' => '{field} is required.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			$name = $this->request->getVar('name');
			$display_name = $this->request->getVar('display_name');
			$show_in_interface = ($this->request->getVar('show_in_interface') != null) ? 1 : 0;
			$include_in_interface_index = ($this->request->getVar('include_in_interface_index') != null) ? 1 : 0;

			try
			{
				$this->dbAdapter->Update($id, (new AttributeFactory())->GetInstanceFromParameters(
					$attribute->name,
					$source_id,
					$display_name,
					$attribute->type,
					$attribute->min,
					$attribute->max,
					$show_in_interface,
					$include_in_interface_index,
					$attribute->storage_location
				));

				if(
					$attribute->display_name != $display_name ||
					$attribute->show_in_interface !== $show_in_interface ||
					$attribute->include_in_interface_index !== $include_in_interface_index
				)
				{
					$dataPipeline = new DataPipeLine($attribute->source_id);
					$dataPipeline->CreateUIIndex($this->authenticator->GetUserId());
				}

				$this->setStatusMessage("Attribute '$name' was updated.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem updating '$name'.", STATUS_ERROR);
			}

			return redirect()->to(base_url($this->controllerName . '/List/' . $source_id));
		}
		else
		{
			$uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

			$uidata->data['name'] = array(
				'name' => 'name',
				'id' => 'name',
				'type' => 'text',
				'class' => 'form-control',
				'readonly' => 'true', // Don't allow the user to edit the attribute name
				'value' => set_value('name', $attribute->name),
			);

			$uidata->data['display_name'] = array(
				'name' => 'display_name',
				'id' => 'display_name',
				'type' => 'text',
				'class' => 'form-control',
				'value' => set_value('name', $attribute->display_name),
			);

			$uidata->data['show_in_interface'] = array(
				'name' => 'show_in_interface[]',
				'id' => 'show_in_interface',
				'class' => 'form-check-input',
				'value' => is_array($show_in_interface_val = set_value('show_in_interface[]', $attribute->show_in_interface)) ? $show_in_interface_val[0] : $attribute->show_in_interface,
				'checked' => (bool)$attribute->show_in_interface
			);

			$uidata->data['include_in_interface_index'] = array(
				'name' => 'include_in_interface_index[]',
				'id' => 'include_in_interface_index',
				'class' => 'form-check-input',
				'value' => is_array($include_in_interface_index_val = set_value('include_in_interface_index[]', $attribute->include_in_interface_index)) ? $include_in_interface_index_val[0] : $attribute->include_in_interface_index,
				'checked' => (bool)$attribute->include_in_interface_index
			);

			$data = $this->wrapData($uidata);
			return view($this->viewDirectory . '/Update', $data);
		}
	}

	public function Details(int $id)
	{
		$attribute = $this->dbAdapter->SetModel(AttributeDetails::class)->Read($id);
		if ($attribute->isNull())
		{
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Attribute Details';
		$uidata->data['attribute'] = $attribute;

		$data = $this->wrapData($uidata);
		return view($this->viewDirectory . '/Details', $data);
	}

	public function OntologyAssociations(int $id)
	{
		$attribute = $this->dbAdapter->SetModel(AttributeAssociation::class)->Read($id);
		if ($attribute->isNull())
		{
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Ontology Associations';
		$uidata->data['attribute'] = $attribute;

		$uidata->IncludeJavaScript(JS . 'cafevariome/attribute.js');
		$uidata->IncludeDataTables();

		if ($this->request->getPost())
		{
			$ontology_id = $this->request->getVar('ontology');
			$prefix_id = $this->request->getVar('prefix');
			$relationship_id = $this->request->getVar('relationship');

			try
			{
				if (!$this->dbAdapter->AssociationExists($id, $ontology_id, $prefix_id, $relationship_id))
				{
					$this->dbAdapter->CreateOntologyAssociation($id, $prefix_id, $relationship_id, $ontology_id);
					$this->setStatusMessage("Attribute association was created.", STATUS_SUCCESS);

					return redirect()->to(base_url($this->controllerName . '/OntologyAssociations/' . $id));
				}
				else
				{
					//Association already exists and cannot be created.
					$uidata->data['statusMessage'] = "Ontology association already exists.";
				}
			}
			catch (\Exception $ex)
			{
				$uidata->data['statusMessage'] = "There was a problem in associating the attribute with the selected ontology prefix and ontology relationship.";
			}
		}

		$attributeOntologyAssociations = $this->dbAdapter->ReadOntologyPrefixesAndRelationships($id);

		$uidata->data['attributeOntologyAssociations'] = $attributeOntologyAssociations;

		$ontologyAdapter = (new OntologyAdapterFactory())->GetInstance();
		$ontologies = $ontologyAdapter->SetModel(OntologyDropDown::class)->ReadAll();

		$ontology_data = [0 => 'Please select an ontology:'];
		$relationship_data = [0 => 'Please select an ontology to load relationships.'];
		$prefix_data = [0 => 'Please select an ontology to load prefixes.'];

		foreach ($ontologies as $ontology)
		{
			$ontology_data[$ontology->getID()] = $ontology->name;
		}

		$uidata->data['ontology'] = [
			'id' => 'ontology',
			'name' => 'ontology',
			'type' => 'dropdown',
			'options' => $ontology_data,
			'class' => 'form-select'
		];

		$uidata->data['relationship'] = [
			'id' => 'relationship',
			'name' => 'relationship',
			'type' => 'dropdown',
			'options' => $relationship_data,
			'class' => 'form-select'
		];

		$uidata->data['prefix'] = [
			'id' => 'prefix',
			'name' => 'prefix',
			'type' => 'dropdown',
			'options' => $prefix_data,
			'class' => 'form-select'
		];


		$data = $this->wrapData($uidata);

		return view($this->viewDirectory . '/OntologyAssociations', $data);
	}

	public function DeleteAssociation(int $association_id)
	{
		$ontologyAdapter = (new OntologyAdapterFactory())->GetInstance();
		$association = $ontologyAdapter->ReadAttributeOntologyAssociation($association_id);
		if ($association == null || $association_id <= 0)
		{
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Delete Ontology Association';
		$uidata->data['association_id'] = $association_id;
		$uidata->data['attribute_id'] = $association['attribute_id'];
		$uidata->data['attribute_name'] = $association['attribute_name'];
		$uidata->data['ontology_name'] = $association['ontology_name'];
		$uidata->data['prefix_name'] = $association['prefix_name'];
		$uidata->data['relationship_name'] = $association['relationship_name'];

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
					$association = $ontologyAdapter->ReadAttributeOntologyAssociation($association_id);
					if ($association)
					{
						$attribute_id = $association['attribute_id'];
						$ontologyAdapter->DeleteAttributeOntologyAssociation($association_id);
						$this->setStatusMessage("Ontology association was deleted.", STATUS_SUCCESS);
						return redirect()->to(base_url($this->controllerName.'/OntologyAssociations/' . $attribute_id));
					}
					else
					{
						$this->setStatusMessage("Ontology association not exist.", STATUS_ERROR);
					}
				}
				catch (\Exception $ex)
				{
					$this->setStatusMessage("There was a problem deleting the ontology association.", STATUS_ERROR);
				}
			}
			return redirect()->to(base_url('Source'));
		}

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory.'/DeleteAssociation', $data);
	}
}
