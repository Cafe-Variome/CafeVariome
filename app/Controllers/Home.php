<?php namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UIData;
use App\Models\cms_model;

class Home extends CVUI_Controller
{

	public function index()
	{
		$udata = new UIData();
		$udata->title = "Home";
		$data = $this->wrapData($udata);

		var_dump(shell_exec("php " . getcwd() . "/index.php Task phenoPacketInsert 7"));
		exit;
		echo view('home/index', $data);
	}


	//--------------------------------------------------------------------

}
