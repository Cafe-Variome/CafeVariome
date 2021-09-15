<?php namespace App\Controllers;

use App\Models\UIData;
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

	public function List(int $source_id = 0)
	{
		$uidata = new UIData();
		$uidata->title = "Attributes";

		if ($source_id > 0){
			$attributes = $this->attributeModel->getAttributesBySourceId($source_id);
		}
		else{
			$attributes = $this->attributeModel->getAllAttributes();
		}

		$uidata->data['attributes'] = $attributes;

		$uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
		$uidata->javascript = array(JS.'cafevariome/attribute.js', VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js');

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory.'/List', $data);
	}

}
