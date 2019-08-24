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
		//echo substr(php_uname(), 0, 7);
		//$result = AuthHelper::authPostRequest(array('installation_key' => $this->setting->settingData['installation_key']), $this->setting->settingData['auth_server'] . "network/get_networks_installation_member_of_with_other_installation_details");

		$sql = "select phenotype_attribute, phenotype_values, network_key from local_phenotypes_lookup where network_key='782565b548c00559ba245d70af042f6b'";
        $local_phenotypes = $this->db->query($sql)->getResultArray();

        //var_dump($local_phenotypes);
		$i = 0;
		$my_arr = [];
		$my_arr[$i] = "medelou";
		$i++;
		$my_arr[$i] = "mmmmm";

		var_dump($my_arr);
		$my_arr[$i] = "56456456";
		var_dump($my_arr);
		exit;

		echo view('home/index', $data);
	}


	//--------------------------------------------------------------------

}
