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
		//$eavModel = new \App\Models\EAV($this->db);
		//var_dump($eavModel->retrieveUpdateNeo4j(7));
		//exit;

		//$netModel = new \App\Models\Network($this->db);

		//$result = $netModel->getAllNetworksSourcesBySourceId(4);

		$this->setting = Settings::getInstance($this->db);

		$result = AuthHelper::authPostRequest(array('installation_key' => $this->setting->settingData['installation_key']), $this->setting->settingData['auth_server'] . "network/get_networks_installation_member_of_with_other_installation_details");
		var_dump($result);
		exit;

		echo view('home/index', $data);
	}


	//--------------------------------------------------------------------

}
