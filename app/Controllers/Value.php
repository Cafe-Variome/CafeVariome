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
}
