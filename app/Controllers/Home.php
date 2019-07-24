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
		$kc = new \App\Libraries\KeyCloak();
		var_dump($kc->checkKeyCloakServer());
		echo view('home/index', $data);
	}


	//--------------------------------------------------------------------

}
