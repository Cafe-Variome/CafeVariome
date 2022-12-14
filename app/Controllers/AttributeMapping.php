<?php namespace App\Controllers;

use App\Libraries\CafeVariome\Database\AttributeAdapter;
use App\Libraries\CafeVariome\Database\SourceAdapter;
use App\Libraries\CafeVariome\Factory\AttributeAdapterFactory;
use App\Libraries\CafeVariome\Factory\SourceAdapterFactory;
use App\Models\UIData;
use CodeIgniter\Config\Services;

/**
 * AttributeMapping.php
 * Created 21/10/2021
 *
 * This class offers CRUD operation for AttributeMappings.
 * @author Mehdi Mehtarizadeh
 */


class AttributeMapping extends CVUI_Controller
{
	private AttributeAdapter $attributeAdapter;
	private SourceAdapter $sourceAdapter;
	private \App\Models\AttributeMapping $attributeMappingModel;
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

		$this->attributeAdapter = (new AttributeAdapterFactory())->GetInstance();
		$this->sourceAdapter = (new SourceAdapterFactory())->GetInstance();
		$this->attributeMappingModel = new \App\Models\AttributeMapping();
		$this->validation = Services::validation();
	}

	public function Index()
	{
		return redirect()->to(base_url('Source'));
	}

	public function List(int $attribute_id)
	{
		$attribute = $this->attributeAdapter->Read($attribute_id);
		if ($attribute->isNull())
		{
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Attribute Mappings';

		$attributeMappings = $this->attributeMappingModel->getAttributeMappingsByAttributeId($attribute_id);
		$sourceId =  $attribute->source_id;
		$uidata->data['attributeMappings'] = $attributeMappings;
		$uidata->data['attributeId'] = $attribute_id;
		$uidata->data['sourceId'] = $sourceId;
		$source = $this->sourceAdapter->Read($sourceId);
		$uidata->data['sourceName'] = $source->name;
		$uidata->data['attributeName'] = $attribute->name;

		$uidata->css = array(VENDOR . 'datatables/datatables/media/css/jquery.dataTables.min.css');
		$uidata->javascript = array(JS . 'cafevariome/attribute_mapping.js', VENDOR . 'datatables/datatables/media/js/jquery.dataTables.min.js');

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory . '/List', $data);
	}

	public function Create(int $attribute_id)
	{
		$attribute = $this->attributeAdapter->Read($attribute_id);
		if ($attribute->isNull())
		{
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Create Attribute Mapping';

		$attributeName = $attribute->name;
		$uidata->data['sourceId'] = $attribute->source_id;
		$uidata->data['attributeId'] = $attribute_id;
		$uidata->data['attributeName'] = $attributeName;

		$this->validation->setRules([
			'name' => [
				'label'  => 'Name',
				'rules'  => 'required|alpha_numeric_punct|duplicate_attribute_and_mapping[source_id]|unique_attribute_mapping[source_id]|max_length[100]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_numeric_punct' => 'The only valid characters for {field} are alphabetical characters, numbers, and spaces.',
					'duplicate_attribute_and_mapping' => 'The mapping name already exists as an attribute within the source.',
					'unique_attribute_mapping' => '{field} has already been mapped to an attribute.',
					'max_length' => 'Maximum length is 100 characters.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			try
			{
				$name = $this->request->getVar('name');

				$this->attributeMappingModel->createAttributeMapping($name, $attribute_id);

				$this->setStatusMessage("Attribute mapping '$name' was created for '$attributeName'.", STATUS_SUCCESS);

			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem creating ' '."  . $ex->getMessage(), STATUS_ERROR);
			}

			return redirect()->to(base_url($this->controllerName.'/List/' . $attribute_id));
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

		return view($this->viewDirectory . '/Create', $data);
	}

	public function Delete(int $id)
	{
		$attributeMapping = $this->attributeMappingModel->getAttributeMapping($id);
		if ($attributeMapping == null || $id <= 0) {
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Delete Attribute Mapping';

		$attributeId = $attributeMapping['attribute_id'];
		$uidata->data['attributeId'] = $attributeId;
		$uidata->data['attributeMappingId'] = $attributeMapping['id'];
		$uidata->data['attributeMappingName'] = $attributeMapping['name'];

		$this->validation->setRules([
			'confirm' => [
				'label'  => 'confirmation',
				'rules'  => 'required',
				'errors' => [
					'required' => '{field} is required.'
				]
			],

			'attribute_mapping_id' => [
				'label'  => 'Attribute Mapping Id',
				'rules'  => 'required|numeric',
				'errors' => [
					'required' => '{field} is required.',
					'numeric' => '{field} must only contain numeric characters.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
			$attributeMappingId = $this->request->getVar('attribute_mapping_id');
			$confirm = $this->request->getVar('confirm');
			if ($confirm == 'yes') {
				try {
					$attributeMapping = $this->attributeMappingModel->getAttributeMapping($attributeMappingId);
					if ($attributeMapping)  {
						$attributeMappingName = $attributeMapping['name'];
						$this->attributeMappingModel->deleteAttributeMapping($attributeMappingId);

						$this->setStatusMessage("Attribute mapping '$attributeMappingName' was deleted.", STATUS_SUCCESS);
					}
					else{
						$this->setStatusMessage("Attribute mapping does not exist.", STATUS_ERROR);
					}
				} catch (\Exception $ex) {
					$this->setStatusMessage("There was a problem deleting the attribute mapping.", STATUS_ERROR);
				}
			}
			return redirect()->to(base_url($this->controllerName.'/List/' . $attributeId));
		}
		else {
			$data = $this->wrapData($uidata);

			return view($this->viewDirectory.'/Delete', $data);
		}
	}
}
