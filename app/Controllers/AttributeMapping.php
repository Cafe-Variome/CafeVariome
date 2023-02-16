<?php namespace App\Controllers;

use App\Libraries\CafeVariome\Database\AttributeAdapter;
use App\Libraries\CafeVariome\Database\SourceAdapter;
use App\Libraries\CafeVariome\Factory\AttributeAdapterFactory;
use App\Libraries\CafeVariome\Factory\AttributeMappingAdapterFactory;
use App\Libraries\CafeVariome\Factory\AttributeMappingFactory;
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


class AttributeMapping extends CVUIController
{
	private AttributeAdapter $attributeAdapter;
	private SourceAdapter $sourceAdapter;
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

		$this->dbAdapter = (new AttributeMappingAdapterFactory())->GetInstance();
		$this->attributeAdapter = (new AttributeAdapterFactory())->GetInstance();
		$this->sourceAdapter = (new SourceAdapterFactory())->GetInstance();
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

		$sourceId =  $attribute->source_id;
		$uidata->data['attributeMappings'] = $this->dbAdapter->ReadByAttributeId($attribute_id);;
		$uidata->data['attributeId'] = $attribute_id;
		$uidata->data['sourceId'] = $sourceId;
		$source = $this->sourceAdapter->Read($sourceId);
		$uidata->data['sourceName'] = $source->name;
		$uidata->data['attributeName'] = $attribute->name;

		$uidata->IncludeJavaScript(JS . 'cafevariome/attribute_mapping.js');
		$uidata->IncludeDataTables();

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
				'rules'  => 'required|alpha_numeric_punct|duplicate_attribute_and_mapping[source_id]|unique_attribute_mapping[source_id]|max_length[256]',
				'errors' => [
					'required' => '{field} is required.',
					'alpha_numeric_punct' => 'The only valid characters for {field} are alphabetical characters, numbers, and spaces.',
					'duplicate_attribute_and_mapping' => 'The mapping name already exists as an attribute within the source.',
					'unique_attribute_mapping' => '{field} has already been mapped to an attribute.',
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			try
			{
				$name = $this->request->getVar('name');

				$this->dbAdapter->Create((new AttributeMappingFactory())->GetInstanceFromParameters($name, $attribute_id));

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
		$attributeMapping = $this->dbAdapter->Read($id);
		if ($attributeMapping->isNull())
		{
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Delete Attribute Mapping';

		$attributeId = $attributeMapping->attribute_id;
		$uidata->data['attributeId'] = $attributeId;
		$uidata->data['attributeMappingId'] = $attributeMapping->getID();
		$uidata->data['attributeMappingName'] = $attributeMapping->name;

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
					$attributeMappingName = $attributeMapping->name;
					$this->dbAdapter->Delete($id);
					$this->setStatusMessage("Attribute mapping '$attributeMappingName' was deleted.", STATUS_SUCCESS);
				}
				catch (\Exception $ex)
				{
					$this->setStatusMessage("There was a problem deleting the attribute mapping.", STATUS_ERROR);
				}
			}
			return redirect()->to(base_url($this->controllerName.'/List/' . $attributeId));
		}
		else
		{
			$data = $this->wrapData($uidata);
			return view($this->viewDirectory.'/Delete', $data);
		}
	}
}
