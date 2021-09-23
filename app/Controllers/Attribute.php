<?php namespace App\Controllers;

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
	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger){
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
		if ($source_name == null || $source_id <= 0){
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Attributes';

		if ($source_id > 0){
			$attributes = $this->attributeModel->getAttributesBySourceId($source_id);
		}

		foreach ($attributes as &$attribute){
			$attribute['type'] = AttributeHelper::getAttributeType($attribute['type']);
			$attribute['storage_location'] = AttributeHelper::getAttributeStorageLocation($attribute['storage_location']);
		}

		$uidata->data['source_name'] = $source_name;
		$uidata->data['attributes'] = $attributes;

		$uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
		$uidata->javascript = array(JS.'cafevariome/attribute.js', VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js');

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory.'/List', $data);
	}

	public function Update(int $attribute_id)
	{
		$uidata = new UIData();
		$uidata->title = 'Edit Attribute';

		$attribute = $this->attributeModel->getAttributeById($attribute_id);
		if ($attribute == null || $attribute_id <= 0){
			return redirect()->to(base_url('Source'));
		}

		$uidata->data['attribute_id'] = $attribute['id'];
		$source_id = $attribute['source_id'];
		$uidata->data['source_id'] = $source_id;

		$this->validation->setRules([
			'display_name' => [
				'label'  => 'Display Name',
				'rules'  => 'required|alpha_numeric_space',
				'errors' => [
					'required' => '{field} is required.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
			$name = $this->request->getVar('name');
			$display_name = $this->request->getVar('display_name');
			$show_in_interface = ($this->request->getVar('show_in_interface') != null) ? true :  false;
			$include_in_interface_index = ($this->request->getVar('include_in_interface_index') != null) ? true :  false;

			try {
				$this->attributeModel->updateAttribute($attribute_id, $display_name, $show_in_interface, $include_in_interface_index);
				$this->setStatusMessage("Attribute '$name' was updated.", STATUS_SUCCESS);
			}
			catch (\Exception $ex) {
				$this->setStatusMessage("There was a problem updating '$name'.", STATUS_ERROR);
			}
			return redirect()->to(base_url($this->controllerName.'/List/' . $source_id));

		}
		else{
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
			return view($this->viewDirectory.'/Update', $data);
		}
	}

	public function Details(int $attribute_id)
	{
		$attribute = $this->attributeModel->getAttributeById($attribute_id);
		if ($attribute == null || $attribute_id <= 0){
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
		if ($attribute['type'] == ATRRIBUTE_TYPE_NUMERIC_REAL || $attribute['type'] == ATRRIBUTE_TYPE_NUMERIC_INTEGER || $attribute['type'] == ATRRIBUTE_TYPE_NUMERIC_NATURAL){
			$uidata->data['minimum'] = $attribute['min'];
			$uidata->data['maximum'] = $attribute['max'];
		}
		$uidata->data['storage_location'] = AttributeHelper::getAttributeStorageLocation($attribute['storage_location']);
		$uidata->data['show_in_interface'] = $attribute['show_in_interface'];
		$uidata->data['include_in_interface_index'] = $attribute['include_in_interface_index'];

		$data = $this->wrapData($uidata);
		return view($this->viewDirectory.'/Details', $data);
	}
}
