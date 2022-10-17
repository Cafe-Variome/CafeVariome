<?php namespace App\Controllers;

use App\Libraries\CafeVariome\Core\DataPipeLine\DataPipeLine;
use App\Libraries\CafeVariome\Database\AttributeAdapter;
use App\Libraries\CafeVariome\Database\SourceAdapter;
use App\Libraries\CafeVariome\Entities\ViewModels\ValueDetails;
use App\Libraries\CafeVariome\Factory\AttributeAdapterFactory;
use App\Libraries\CafeVariome\Factory\SourceAdapterFactory;
use App\Libraries\CafeVariome\Factory\ValueAdapterFactory;
use App\Libraries\CafeVariome\Factory\ValueFactory;
use App\Models\UIData;
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

	private AttributeAdapter $attributeAdapter;
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
		$this->dbAdapter = (new ValueAdapterFactory())->GetInstance();
		$this->attributeAdapter = (new AttributeAdapterFactory())->GetInstance();
		$this->sourceAdapter = (new SourceAdapterFactory())->GetInstance();
	}

	public function Index()
	{
		return redirect()->to(base_url('Source'));
	}

	public function List(int $attribue_id)
	{
		$attribute = $this->attributeAdapter->Read($attribue_id);
		if ($attribute->isNull())
		{
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = "Values";

		$values = $this->dbAdapter->ReadByAttributeId($attribue_id);
		$source = $this->sourceAdapter->Read($attribute->source_id);
		$uidata->data['source_id'] = $attribute->source_id;
		$uidata->data['source_name'] = $source->name;
		$uidata->data['attribute_name'] = $attribute->name;
		$uidata->data['values'] = $values;

		$uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
		$uidata->javascript = array(JS.'cafevariome/attribute.js', VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js');

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory.'/List', $data);
	}

	public function Update(int $id)
	{
		$value = $this->dbAdapter->Read($id);
		if ($value->isNull())
		{
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Edit Value';
		$attribute_id = $value->attribute_id;
		$attribute = $this->attributeAdapter->Read($attribute_id);
		$uidata->data['attribute_id'] = $attribute_id;
		$uidata->data['value_id'] = $value->getID();

		$this->validation->setRules([
			'display_name' => [
				'label'  => 'Display Name',
				'rules'  => 'required|string',
				'errors' => [
					'required' => '{field} is required.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			$name = $this->request->getVar('name');
			$display_name = $this->request->getVar('display_name');
			$show_in_interface = ($this->request->getVar('show_in_interface') != null) ? true :  false;
			$include_in_interface_index = ($this->request->getVar('include_in_interface_index') != null) ? true :  false;

			try
			{
				//$id, $display_name, $show_in_interface, $include_in_interface_index
				$this->dbAdapter->Update($id,
					(new ValueFactory())->GetInstanceFromParameters($name, $attribute_id, $display_name, $value->frequency, $show_in_interface, $include_in_interface_index)
				);

				if(
					$value->display_name != $display_name ||
					$value->show_in_interface !== $show_in_interface ||
					$value->include_in_interface_index !== $include_in_interface_index
				)
				{
					$dataPipeline = new DataPipeLine($attribute->source_id);
					$dataPipeline->CreateUIIndex($this->authenticator->GetUserId());
				}

				$this->setStatusMessage("Value '$name' was updated.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem updating '$name'.", STATUS_ERROR);
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
				'readonly' => 'true', // Don't allow the user to edit the attribute name
				'value' => set_value('name', html_entity_decode($value->name)),
			);

			$uidata->data['display_name'] = array(
				'name' => 'display_name',
				'id' => 'display_name',
				'type' => 'text',
				'class' => 'form-control',
				'value' => set_value('name', html_entity_decode($value->display_name)),
			);

			$uidata->data['show_in_interface'] = array(
				'name' => 'show_in_interface[]',
				'id' => 'show_in_interface',
				'class' => 'custom-control-input',
				'value' => set_value('show_in_interface', $value->show_in_interface),
				'checked' => $value->show_in_interface ? true : false
			);

			$uidata->data['include_in_interface_index'] = array(
				'name' => 'include_in_interface_index[]',
				'id' => 'include_in_interface_index',
				'class' => 'custom-control-input',
				'value' => set_value('include_in_interface_index', $value->include_in_interface_index),
				'checked' => $value->include_in_interface_index ? true : false
			);

			$data = $this->wrapData($uidata);
			return view($this->viewDirectory.'/Update', $data);
		}
	}

	public function Details(int $id)
	{
		$value = $this->dbAdapter->SetModel(ValueDetails::class)->Read($id);
		if ($value->isNull())
		{
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Attribute Details';
		$uidata->data['value'] = $value;

		$data = $this->wrapData($uidata);
		return view($this->viewDirectory.'/Details', $data);
	}
}
