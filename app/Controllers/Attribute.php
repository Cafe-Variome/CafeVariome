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

}
