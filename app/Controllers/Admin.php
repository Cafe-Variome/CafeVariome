<?php namespace App\Controllers;

/**
 * Admin.php
 * Created 18/07/2019
 * 
 * @author Owen Lancaster
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 */

use App\Models\UIData;
use App\Models\Settings;
use App\Helpers\AuthHelper;

use CodeIgniter\Config\Services;

class Admin extends CVUI_Controller{

    
	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->session = Services::session();
		$this->db = \Config\Database::connect();
        $this->setting =  Settings::getInstance($this->db);

    }

    /**
     * 
     */
    function get_phenotype_attributes_for_network($network_key) {

        $token = $this->session->get('Token');
        $installation_urls = json_decode(AuthHelper::authPostRequest(array('installation_key' => $this->setting->settingData['installation_key'], 'network_key' => $network_key), $this->setting->settingData['auth_server'] . "network/get_all_installation_ips_for_network"), true);
        error_log(print_r($installation_urls,1));

        $postdata = http_build_query(
                array(
                    'network_key' => $network_key,
                    'modification_time' => @filemtime("resources/phenotype_lookup_data/" . "local_" . $network_key . ".json")
                )
        );

        $opts = array('http' =>
            array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 1
            )
        );
        $context = stream_context_create($opts);

        $data = array();
        foreach ($installation_urls as $url) {
            $url = rtrim($url['installation_base_url'], "/") . "/admin/get_json_for_phenotype_lookup/";
            $result = @file_get_contents($url, 1, $context);

            if ($result) {
                foreach (json_decode($result, 1) as $res) {
                    if (array_key_exists($res['attribute'], $data)) {
                        foreach (explode("|", strtolower($res['value'])) as $val) {
                            if (!in_array($val, $data[$res['attribute']]))
                                array_push($data[$res['attribute']], $val);
                        }
                    } else {
                        $data[$res['attribute']] = explode("|", strtolower($res['value']));
                    }
                }
            }
        }

        foreach(array_keys($data) as $key){
            sort($data[$key]);
        }
        ksort($data);

        if ($data) {
            file_put_contents("resources/phenotype_lookup_data/" . "local_" . $network_key . ".json", json_encode($data));
        }
        echo file_get_contents("resources/phenotype_lookup_data/" . "local_" . $network_key . ".json");
    }

}