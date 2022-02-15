<?php namespace App\Controllers;

use App\Libraries\CafeVariome\Helpers\Shell\PHPShellHelper;
use App\Models\UIData;
use App\Libraries\CafeVariome\Helpers\UI\AttributeHelper;
use CodeIgniter\Config\Services;

/**
 * Attribute.php
 * Created 15/09/2021
 *
 * This class offers CRUD operation for data attributes.
 * @author Mehdi Mehtarizadeh
 */

class Attribute extends CVUI_Controller
{
	private $validation;
	private $sourceModel;
	private $attributeModel;

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
		$this->sourceModel = new \App\Models\Source();
		$this->attributeModel = new \App\Models\Attribute();
	}

	public function Index()
	{
		return redirect()->to(base_url('Source'));
	}

	public function List(int $source_id)
	{
		$source_name = $this->sourceModel->getSourceNameByID($source_id);
		if ($source_name == null || $source_id <= 0) {
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Attributes';

		$attributes = $this->attributeModel->getAttributesBySourceId($source_id);

		foreach ($attributes as &$attribute) {
			$attribute['type_text'] = AttributeHelper::getAttributeType($attribute['type']);
			$attribute['storage_location'] = AttributeHelper::getAttributeStorageLocation($attribute['storage_location']);
		}

		$uidata->data['sourceId'] = $source_id;
		$uidata->data['source_name'] = $source_name;
		$uidata->data['attributes'] = $attributes;

		$uidata->css = array(VENDOR . 'datatables/datatables/media/css/jquery.dataTables.min.css');
		$uidata->javascript = array(JS . 'cafevariome/attribute.js', VENDOR . 'datatables/datatables/media/js/jquery.dataTables.min.js');

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory . '/List', $data);
	}

	public function Update(int $attribute_id)
	{
		$uidata = new UIData();
		$uidata->title = 'Edit Attribute';

		$attribute = $this->attributeModel->getAttribute($attribute_id);
		if ($attribute == null || $attribute_id <= 0) {
			return redirect()->to(base_url('Source'));
		}

		$uidata->data['attribute_id'] = $attribute['id'];
		$source_id = $attribute['source_id'];
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

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
			$name = $this->request->getVar('name');
			$display_name = $this->request->getVar('display_name');
			$show_in_interface = ($this->request->getVar('show_in_interface') != null) ? true : false;
			$include_in_interface_index = ($this->request->getVar('include_in_interface_index') != null) ? true : false;

			try {
				$this->attributeModel->updateAttribute($attribute_id, $display_name, $show_in_interface, $include_in_interface_index);

				if(
					$attribute['display_name'] != $display_name ||
					$attribute['show_in_interface'] !== $show_in_interface ||
					$attribute['include_in_interface_index'] !== $include_in_interface_index
				)
				{
					PHPShellHelper::runAsync(getcwd() . "/index.php Task CreateUserInterfaceIndex $source_id");
				}

				$this->setStatusMessage("Attribute '$name' was updated.", STATUS_SUCCESS);
			} catch (\Exception $ex) {
				$this->setStatusMessage("There was a problem updating '$name'.", STATUS_ERROR);
			}
			return redirect()->to(base_url($this->controllerName . '/List/' . $source_id));

		} else {
			$uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

			$uidata->data['name'] = array(
				'name' => 'name',
				'id' => 'name',
				'type' => 'text',
				'class' => 'form-control',
				'readonly' => 'true', // Don't allow the user to edit the attribute name
				'value' => set_value('name', $attribute['name']),
			);

			$uidata->data['display_name'] = array(
				'name' => 'display_name',
				'id' => 'display_name',
				'type' => 'text',
				'class' => 'form-control',
				'value' => set_value('name', $attribute['display_name']),
			);

			$uidata->data['show_in_interface'] = array(
				'name' => 'show_in_interface',
				'id' => 'show_in_interface',
				'class' => 'custom-control-input',
				'value' => set_value('show_in_interface', $attribute['show_in_interface']),
				'checked' => $attribute['show_in_interface'] ? true : false
			);

			$uidata->data['include_in_interface_index'] = array(
				'name' => 'include_in_interface_index',
				'id' => 'include_in_interface_index',
				'class' => 'custom-control-input',
				'value' => set_value('include_in_interface_index', $attribute['include_in_interface_index']),
				'checked' => $attribute['include_in_interface_index'] ? true : false
			);

			$data = $this->wrapData($uidata);
			return view($this->viewDirectory . '/Update', $data);
		}
	}

	public function Details(int $attribute_id)
	{
		$attribute = $this->attributeModel->getAttribute($attribute_id);
		if ($attribute == null || $attribute_id <= 0) {
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Attribute Details';
		$uidata->data['attribute_id'] = $attribute['id'];
		$source_id = $attribute['source_id'];
		$uidata->data['source_id'] = $source_id;
		$uidata->data['source_name'] = $this->sourceModel->getSourceNameById($source_id);
		$uidata->data['name'] = $attribute['name'];
		$uidata->data['display_name'] = $attribute['display_name'];
		$uidata->data['type'] = AttributeHelper::getAttributeType($attribute['type']);
		$uidata->data['minimum'] = null;
		$uidata->data['maximum'] = null;
		if ($attribute['type'] == ATTRIBUTE_TYPE_NUMERIC_REAL || $attribute['type'] == ATTRIBUTE_TYPE_NUMERIC_INTEGER || $attribute['type'] == ATTRIBUTE_TYPE_NUMERIC_NATURAL) {
			$uidata->data['minimum'] = $attribute['min'];
			$uidata->data['maximum'] = $attribute['max'];
		}
		$uidata->data['storage_location'] = AttributeHelper::getAttributeStorageLocation($attribute['storage_location']);
		$uidata->data['show_in_interface'] = $attribute['show_in_interface'];
		$uidata->data['include_in_interface_index'] = $attribute['include_in_interface_index'];

		$data = $this->wrapData($uidata);
		return view($this->viewDirectory . '/Details', $data);
	}

	public function OntologyAssociations(int $attribute_id)
	{
		$attribute = $this->attributeModel->getAttribute($attribute_id);
		if ($attribute == null || $attribute_id <= 0 || $attribute['type'] != ATTRIBUTE_TYPE_ONTOLOGY_TERM) {
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Ontology Associations';
		$uidata->data['attribute_name'] = $attribute['name'];
		$uidata->data['attribute_id'] = $attribute_id;
		$source_id = $this->attributeModel->getSourceIdByAttributeId($attribute_id);
		$source_name = $this->sourceModel->getSourceNameByID($source_id);
		$uidata->data['source_id'] = $source_id;
		$uidata->data['source_name'] = $source_name;

		$uidata->css = array(VENDOR . 'datatables/datatables/media/css/jquery.dataTables.min.css');
		$uidata->javascript = array(JS . 'cafevariome/attribute.js', VENDOR . 'datatables/datatables/media/js/jquery.dataTables.min.js');

		if ($this->request->getPost()) {
			$ontology_id = $this->request->getVar('ontology');
			$prefix_id = $this->request->getVar('prefix');
			$relationship_id = $this->request->getVar('relationship');

			try {
				if (!$this->attributeModel->attributeAssociationExists($attribute_id, $ontology_id, $prefix_id, $relationship_id)) {
					$this->attributeModel->associateAttributeWithOntologyPrefixAndRelationship($attribute_id, $prefix_id, $relationship_id, $ontology_id);
					$this->setStatusMessage("Attribute association was created.", STATUS_SUCCESS);

					return redirect()->to(base_url($this->controllerName . '/OntologyAssociations/' . $attribute_id));
				} else {
					//Association already exists and cannot be created.
					$uidata->data['statusMessage'] = "Ontology association already exists.";
				}
			} catch (\Exception $ex) {
				$uidata->data['statusMessage'] = "There was a problem in associating the attribute with the selected ontology prefix and ontology relationship.";
			}
		}

		$attributeOntologyAssociations = $this->attributeModel->getOntologyPrefixesAndRelationshipsByAttributeId($attribute_id);

		$uidata->data['attributeOntologyAssociations'] = $attributeOntologyAssociations;

		$ontologyModel = new \App\Models\Ontology();
		$ontologies = $ontologyModel->getOntologies();

		$ontology_data = [0 => 'Please select an ontology:'];
		$relationship_data = [0 => 'Please select an ontology to load relationships.'];
		$prefix_data = [0 => 'Please select an ontology to load prefixes.'];

		foreach ($ontologies as $ontology) {
			$ontology_data[$ontology['id']] = $ontology['name'];
		}

		$uidata->data['ontology'] = [
			'id' => 'ontology',
			'name' => 'ontology',
			'type' => 'dropdown',
			'options' => $ontology_data,
			'class' => 'form-control'
		];

		$uidata->data['relationship'] = [
			'id' => 'relationship',
			'name' => 'relationship',
			'type' => 'dropdown',
			'options' => $relationship_data,
			'class' => 'form-control'
		];

		$uidata->data['prefix'] = [
			'id' => 'prefix',
			'name' => 'prefix',
			'type' => 'dropdown',
			'options' => $prefix_data,
			'class' => 'form-control'
		];

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory . '/OntologyAssociations', $data);
	}

	public function DeleteAssociation(int $association_id)
	{
		$association = $this->attributeModel->getAttributeOntologyAssociation($association_id);
		if ($association == null || $association_id <= 0){
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
			],

			'association_id' => [
				'label'  => 'Association Id',
				'rules'  => 'required|alpha_dash',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_dash' => '{field} must only contain alpha-numeric characters, underscores, or dashes.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
			$association_id = $this->request->getVar('association_id');
			$confirm = $this->request->getVar('confirm');
			if ($confirm == 'yes') {
				try {
					$association = $this->attributeModel->getAttributeOntologyAssociation($association_id);
					if ($association)  {
						$attribute_id = $association['attribute_id'];
						$this->attributeModel->deleteAttributeOntologyAssociation($association_id);
						$this->setStatusMessage("Ontology association was deleted.", STATUS_SUCCESS);
						return redirect()->to(base_url($this->controllerName.'/OntologyAssociations/' . $attribute_id));
					}
					else{
						$this->setStatusMessage("Ontology association not exist.", STATUS_ERROR);
					}
				} catch (\Exception $ex) {
					$this->setStatusMessage("There was a problem deleting the ontology association.", STATUS_ERROR);
				}
			}
			return redirect()->to(base_url('Source'));
		}


		$data = $this->wrapData($uidata);

		return view($this->viewDirectory.'/DeleteAssociation', $data);
	}
}
