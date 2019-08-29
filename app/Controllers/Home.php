<?php namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UIData;
use App\Models\cms_model;
use App\Models\Settings;
use App\Helpers\AuthHelper;

class Home extends CVUI_Controller
{

	public function index()
	{
		$this->db = \Config\Database::connect();

		$udata = new UIData();
		$udata->title = "Home";
		$data = $this->wrapData($udata);

		echo view('home/index', $data);
	}


	//--------------------------------------------------------------------

}
