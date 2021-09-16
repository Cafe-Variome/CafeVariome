<?php namespace App\Controllers;

use App\Models\UIData;
use App\Libraries\CafeVariome\Helpers\UI\AttributeHelper;
use CodeIgniter\Config\Services;

/**
 * Value.php
 * Created 15/09/2021
 *
 * This class offers CRUD operation for data values.
 * @author Mehdi Mehtarizadeh
 */

class Value extends CVUI_Controller
{
	private $validation;
	private $attributeModel;
	private $valueModel;

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
		$this->attributeModel = new \App\Models\Attribute();
		$this->valueModel = new \App\Models\Value();
	}

	public function Index()
	{
		return redirect()->to(base_url('Source'));
	}

	public function List(int $attribute_id)
	{
		$uidata = new UIData();
		$uidata->title = "Values";

		$attribute_name = $this->attributeModel->getAttributeNameById($attribute_id);
		$source_id = $this->attributeModel->getSourceIdByAttributeId($attribute_id);

		if ($attribute_name == null || $attribute_id <= 0){
			return redirect()->to(base_url('Source'));
		}
		$values = $this->valueModel->getValuesByAttributeId($attribute_id);

		$uidata->data['source_id'] = $source_id;
		$uidata->data['attribute_name'] = $attribute_name;
		$uidata->data['values'] = $values;

		$uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
		$uidata->javascript = array(JS.'cafevariome/attribute.js', VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js');

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory.'/List', $data);
	}

	public function Update(int $value_id)
	{
		$value = $this->valueModel->getValueById($value_id);
		if ($value == null || $value_id <= 0){
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Edit Value';
		$attribute_id = $value['attribute_id'];
		$uidata->data['attribute_id'] = $attribute_id;
		$uidata->data['value_id'] = $value['id'];

		$this->validation->setRules([
			'display_name' => [
				'label'  => 'Display Name',
				'rules'  => 'required|string',
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
				$this->valueModel->updateValue($value_id, $display_name, $show_in_interface, $include_in_interface_index);
				$this->setStatusMessage("Value '$name' was updated.", STATUS_SUCCESS);
			}
			catch (\Exception $ex) {
				$this->setStatusMessage("There was a problem updating '$name'.", STATUS_ERROR);
			}
			return redirect()->to(base_url($this->controllerName.'/List/' . $attribute_id));

		}
		else{
			$uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

			$uidata->data['name'] = array(
				'name' => 'name',
				'id' => 'name',
				'type' => 'text',
				'class' => 'form-control',
				'readonly' => 'true', // Don't allow the user to edit the attribute name
				'value' => set_value('name', $value['name']),
			);

			$uidata->data['display_name'] = array(
				'name' => 'display_name',
				'id' => 'display_name',
				'type' => 'text',
				'class' => 'form-control',
				'value' => set_value('name', $value['display_name']),
			);

			$uidata->data['show_in_interface'] = array(
				'name' => 'show_in_interface',
				'id' => 'show_in_interface',
				'class' => 'custom-control-input',
				'value' => set_value('show_in_interface', $value['show_in_interface']),
				'checked' => $value['show_in_interface'] ? true : false
			);

			$uidata->data['include_in_interface_index'] = array(
				'name' => 'include_in_interface_index',
				'id' => 'include_in_interface_index',
				'class' => 'custom-control-input',
				'value' => set_value('include_in_interface_index', $value['include_in_interface_index']),
				'checked' => $value['include_in_interface_index'] ? true : false
			);

			$data = $this->wrapData($uidata);
			return view($this->viewDirectory.'/Update', $data);
		}
	}

	public function Details(int $value_id)
	{
		$value = $this->valueModel->getValueById($value_id);
		if ($value == null || $value <= 0){
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Attribute Details';
		$uidata->data['value_id'] = $value['id'];
		$attribute_id = $value['attribute_id'];
		$uidata->data['attribute_id'] = $attribute_id;
		$uidata->data['attribute_name'] = $this->attributeModel->getAttributeNameById($attribute_id);
		$uidata->data['name'] = $value['name'];
		$uidata->data['display_name'] = $value['display_name'];
		$uidata->data['show_in_interface'] = $value['show_in_interface'];
		$uidata->data['include_in_interface_index'] = $value['include_in_interface_index'];

		$data = $this->wrapData($uidata);
		return view($this->viewDirectory.'/Details', $data);
	}
}
