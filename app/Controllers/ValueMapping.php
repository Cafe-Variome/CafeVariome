<?php namespace App\Controllers;

use App\Models\UIData;
use CodeIgniter\Config\Services;

/**
 * ValueMapping.php
 * Created 08/11/2021
 *
 * This class offers CRUD operation for ValueMappings.
 * @author Mehdi Mehtarizadeh
 */

class ValueMapping extends CVUI_Controller
{
	private $attributeModel;
	private $valueModel;
	private $valueMappingModel;
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

		$this->attributeModel = new \App\Models\Attribute();
		$this->valueModel = new \App\Models\Value();
		$this->valueMappingModel = new \App\Models\ValueMapping();
		$this->validation = Services::validation();
	}

	public function Index()
	{
		return redirect()->to(base_url('Source'));
	}

	public function List(int $value_id)
	{
		$value = $this->valueModel->getValue($value_id);
		if ($value == null || $value_id <= 0) {
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Value Mappings';

		$uidata->css = array(VENDOR . 'datatables/datatables/media/css/jquery.dataTables.min.css');
		$uidata->javascript = array(JS . 'cafevariome/value_mapping.js', VENDOR . 'datatables/datatables/media/js/jquery.dataTables.min.js');

		$valueMappings = $this->valueMappingModel->getValueMappingsByValueId($value_id);

		$attributeId = $value['attribute_id'];
		$uidata->data['valueMappings'] = $valueMappings;
		$uidata->data['valueId'] = $value_id;
		$uidata->data['valueName'] = $value['name'];
		$uidata->data['attributeId'] = $attributeId;
		$attributeName = $this->attributeModel->getAttributeNameById($attributeId);
		$uidata->data['attributeName'] = $attributeName;

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory . '/List', $data);
	}

	public function Create(int $value_id)
	{
		$value = $this->valueModel->getValue($value_id);
		if ($value == null || $value_id <= 0) {
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Create Value Mapping';

		$valueName = $value['name'];
		$uidata->data['valueId'] = $value_id;
		$uidata->data['valueName'] = $valueName;

		$this->validation->setRules([
			'name' => [
				'label'  => 'Name',
				'rules'  => 'required|alpha_numeric_punct|is_unique[value_mappings.name]|max_length[100]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_numeric_punct' => 'The only valid characters for {field} are alphabetical characters, numbers, and spaces.',
					'is_unique' => '{field} already exists.',
					'max_length' => 'Maximum length is 100 characters.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
			try {
				$name = $this->request->getVar('name');
				$this->valueMappingModel->createValueMapping($name, $value_id);
				$this->setStatusMessage("Value mapping '$name' was created for '$valueName'.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem creating ' '."  . $ex->getMessage(), STATUS_ERROR);
			}

			return redirect()->to(base_url($this->controllerName.'/List/' . $value_id));
		}
		else{
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

		return view($this->viewDirectory . '/Create', $data);
	}

	public function Delete(int $id)
	{
		$valueMapping = $this->valueMappingModel->getValueMapping($id);
		if ($valueMapping == null || $id <= 0) {
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Delete Value Mapping';

		$valueId = $valueMapping['value_id'];
		$uidata->data['valueId'] = $valueId;
		$uidata->data['valueMappingId'] = $valueMapping['id'];
		$uidata->data['valueMappingName'] = $valueMapping['name'];

		$this->validation->setRules([
			'confirm' => [
				'label'  => 'confirmation',
				'rules'  => 'required',
				'errors' => [
					'required' => '{field} is required.'
				]
			],

			'value_mapping_id' => [
				'label'  => 'Value Mapping Id',
				'rules'  => 'required|numeric',
				'errors' => [
					'required' => '{field} is required.',
					'numeric' => '{field} must only contain numeric characters.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
			$valueMappingId = $this->request->getVar('value_mapping_id');
			$confirm = $this->request->getVar('confirm');
			if ($confirm == 'yes') {
				try {
					$valueMapping = $this->valueMappingModel->getValueMapping($valueMappingId);
					if ($valueMapping)  {
						$valueMappingName = $valueMapping['name'];
						$this->valueMappingModel->deleteValueMapping($valueMappingId);

						$this->setStatusMessage("Value mapping '$valueMappingName' was deleted.", STATUS_SUCCESS);
					}
					else{
						$this->setStatusMessage("Value mapping does not exist.", STATUS_ERROR);
					}
				} catch (\Exception $ex) {
					$this->setStatusMessage("There was a problem deleting the value mapping.", STATUS_ERROR);
				}
			}
			return redirect()->to(base_url($this->controllerName.'/List/' . $valueId));
		}
		else {
			$data = $this->wrapData($uidata);

			return view($this->viewDirectory.'/Delete', $data);
		}
	}

}
