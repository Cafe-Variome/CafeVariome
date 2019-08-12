<?php namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UIData;
use App\Models\cms_model;

class Home extends CVUI_Controller
{

	public function index()
	{
		$this->db = \Config\Database::connect();

		$udata = new UIData();
		$udata->title = "Home";
		$data = $this->wrapData($udata);
		$eavModel = new \App\Models\EAV($this->db);
		var_dump($eavModel->retrieveUpdateNeo4j(7));
		exit;
		echo view('home/index', $data);
	}


	//--------------------------------------------------------------------

}
