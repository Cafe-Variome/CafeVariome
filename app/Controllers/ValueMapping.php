<?php namespace App\Controllers;

/**
 * ValueMapping.php
 * Created 08/11/2021
 * @deprecated
 * This class offers CRUD operation for ValueMappings.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\AttributeAdapter;
use App\Libraries\CafeVariome\Database\ValueAdapter;
use App\Libraries\CafeVariome\Factory\AttributeAdapterFactory;
use App\Libraries\CafeVariome\Factory\ValueAdapterFactory;
use App\Libraries\CafeVariome\Factory\ValueMappingAdapterFactory;
use App\Libraries\CafeVariome\Factory\ValueMappingFactory;
use App\Models\UIData;
use CodeIgniter\Config\Services;

class ValueMapping extends CVUIController
{
	private AttributeAdapter $attributeAdapter;
	private ValueAdapter $valueAdapter;
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
		$this->valueAdapter = (new ValueAdapterFactory())->GetInstance();
		$this->dbAdapter = (new ValueMappingAdapterFactory())->GetInstance();
		$this->validation = Services::validation();
	}

	public function Index()
	{
		return redirect()->to(base_url('Source'));
	}

	public function List(int $value_id)
	{
		$value = $this->valueAdapter->Read($value_id);
		if ($value->isNull())
		{
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Value Mappings';

		$uidata->IncludeJavaScript(JS.'cafevariome/value_mapping.js');
		$uidata->IncludeDataTables();

		$valueMappings = $this->dbAdapter->ReadByValueId($value_id);
		$attributeId = $value->attribute_id;
		$attribute = $this->attributeAdapter->Read($attributeId);

		$uidata->data['valueMappings'] = $valueMappings;
		$uidata->data['valueId'] = $value_id;
		$uidata->data['valueName'] = $value->name;
		$uidata->data['attributeId'] = $attributeId;
		$uidata->data['attributeName'] = $attribute->name;

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory . '/List', $data);
	}

	public function Create(int $value_id)
	{
		$value = $this->valueAdapter->Read($value_id);
		if ($value->isNull())
		{
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Create Value Mapping';

		$valueName = $value->name;
		$uidata->data['attributeId'] = $value->attribute_id;
		$uidata->data['valueId'] = $value_id;
		$uidata->data['valueName'] = $valueName;

		$this->validation->setRules([
			'name' => [
				'label'  => 'Name',
				'rules'  => 'required|alpha_numeric_punct|duplicate_value_and_mapping[attribute_id]|unique_value_mapping[attribute_id]|max_length[100]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_numeric_punct' => 'The only valid characters for {field} are alphabetical characters, numbers, and spaces.',
					'duplicate_value_and_mapping' => 'The mapping name already exists as a value within the attribute.',
					'unique_value_mapping' => '{field} has already been mapped to a value.',
					'max_length' => 'Maximum length is 100 characters.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			try
			{
				$name = $this->request->getVar('name');
				$this->dbAdapter->Create(
					(new ValueMappingFactory())->GetInstanceFromParameters($name, $value_id)
				);
				$this->setStatusMessage("Value mapping '$name' was created for '$valueName'.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem creating value mapping: "  . $ex->getMessage(), STATUS_ERROR);
			}

			return redirect()->to(base_url($this->controllerName.'/List/' . $value_id));
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
		$valueMapping = $this->dbAdapter->Read($id);
		if ($valueMapping->isNull())
		{
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Delete Value Mapping';

		$valueId = $valueMapping->value_id;
		$uidata->data['valueId'] = $valueId;
		$uidata->data['valueMappingId'] = $valueMapping->getID();
		$uidata->data['valueMappingName'] = $valueMapping->name;

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

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			$confirm = $this->request->getVar('confirm');
			if ($confirm == 'yes')
			{
				try
				{
					$valueMappingName = $valueMapping->name;
					$this->dbAdapter->Delete($id);
					$this->setStatusMessage("Value mapping '$valueMappingName' was deleted.", STATUS_SUCCESS);
				} catch (\Exception $ex)
				{
					$this->setStatusMessage("There was a problem deleting the value mapping.", STATUS_ERROR);
				}
			}
			return redirect()->to(base_url($this->controllerName.'/List/' . $valueId));
		}
		else
		{
			$data = $this->wrapData($uidata);
			return view($this->viewDirectory.'/Delete', $data);
		}
	}
}
