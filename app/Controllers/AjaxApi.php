<?php namespace App\Controllers;

/**
 * AjaxApi.php 
 * 
 * Created 15/08/2019
 * 
 * @author Mehdi Mehtraizadeh
 * @author Gregory Warren
 * @author Owen Lancaster
 * 
 * This controller contains listener methods for client-side ajax requests.
 * Methods in this controller were formerly in other controllers. 
 * Code must be more secure. Some of the methods here must be moved to back-end layers for security reasons.
 */

use CodeIgniter\Controller;
use App\Helpers\AuthHelper;
use App\Models\Settings;

 class AjaxApi extends Controller{

	protected $db;

	protected $setting;

    /**
	 * Constructor
	 *
	 */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger){
		parent::initController($request, $response, $logger);
		$this->db = \Config\Database::connect();

        $this->setting =  Settings::getInstance($this->db);

    }

    function query($network_key = '') {
        $api = json_encode($this->request->getVar('jsonAPI'));

        $cafeVariomeQuery = new \App\Libraries\CafeVariome\Query();
        $resp['Phenopackets'] = $cafeVariomeQuery->search($api, $network_key);

        return json_encode($resp);
    }

    function hpo_query($id = ''){
        if($id) {
            return file_get_contents("https://www240.lamp.le.ac.uk/hpo/query.php?id=" . $id);
        }
        else {
            return file_get_contents("https://www240.lamp.le.ac.uk/hpo/query.php");
        }
    }

    function build_tree() {
        if ($this->request->isAJAX())
        {
            $hpo_json = json_decode(stripslashes($this->request->getVar('hpo_json')), 1);
            // $hpo_json = $_POST['hpo_json'];
            $hpo_json = json_decode(str_replace('"true"', 'true', json_encode($hpo_json)), 1);
            $hpo_json = json_decode(str_replace('"false"', 'false', json_encode($hpo_json)), 1);
    
            error_log(json_encode($hpo_json));
    
            $ancestry = $this->request->getVar('ancestry');
            $hp_term = explode(' ', $this->request->getVar('hp_term'))[0];
    
            error_log($ancestry);
    
            $splits = explode('||', $ancestry);
            foreach ($splits as $split) {
                $parent = &$hpo_json;
    
                $ancestor = explode('|', $split);
                $str = 'HP:0000001';
                foreach(array_reverse($ancestor) as $term) {
                    if($term === 'HP:0000001') continue;
    
                    $str .= ".$term";
                    if(array_key_exists('children', $parent) && is_array($parent['children'])) {
                        foreach($parent['children'] as &$child) {
                            if($child['id'] === $str) {
                                $parent = &$child;
                                if(array_key_exists('children', $parent) && is_array($child['children'])) {
                                    $parent['children'] = &$child['children'];
                                } else {
                                    $parent['state']['opened'] = "true";
    
                                    unset($parent['state']['loading']);
                                    unset($parent['state']['loaded']);
                                    $dat = file_get_contents("https://www240.lamp.le.ac.uk/hpo/query.php?id=" . $str);
                                    $parent['children'] = json_decode($dat, 1);
    
                                }
                                break;
                            }
                        }
                    }
                }
                if(array_key_exists('children', $parent)) {
                    foreach($parent['children'] as &$child) {
                        if($child['id'] === $str . ".$hp_term") {
                            $child['state']['selected'] = "true";
                        }
                    }
                }
            }
            $hpo_json = str_replace('"true"', 'true', $hpo_json);
            $hpo_json = str_replace('"false"', 'false', $hpo_json);
    
            return json_encode($hpo_json);
        }

	}

	/**
     * getPhenotypeAttributes
     * @param string network_key 
     * @return string in json format, phenotype and hpo data
     * 
     */
    function getPhenotypeAttributes(string $network_key) {
        if ($this->request->isAJAX()) {
            $installation_urls = json_decode(AuthHelper::authPostRequest(array('installation_key' => $this->setting->settingData['installation_key'], 'network_key' => $network_key), $this->setting->settingData['auth_server'] . "network/get_all_installation_ips_for_network"), true);

            $postdata = http_build_query(
                array(
                    'network_key' => $network_key,
                    'modification_time' => @filemtime("resources/phenotype_lookup_data/local_" . $network_key . ".json")
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
                $url = rtrim($url['installation_base_url'], "/") . "/AjaxApi/get_json_for_phenotype_lookup";
                try{
                    $result = @file_get_contents($url, 1, $context);
                }
                catch (\Exception $ex) {
                    return json_encode(var_dump($ex));
                }
                if ($result) {
                    foreach (json_decode($result, 1) as $res) {
    
                        if (array_key_exists($res['attribute'], $data)) {
                            foreach (explode("|", strtolower($res['value'])) as $val) {
                                if (!in_array($val, $data[$res['attribute']]))
                                    array_push($data[$res['attribute']], $val);
                            }
                        }
                        else {
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
                file_put_contents("resources/phenotype_lookup_data/local_" . $network_key . ".json", json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE));
            }
    
            // HPO ancestry
            $postdata = http_build_query(
                ['network_key' => $network_key,
                    'modification_time' => @filemtime("resources/phenotype_lookup_data/" . "local_" . $network_key . "_hpo_ancestry.json")]
            );
    
            $opts = ['http' =>
                [
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $postdata,
                    'timeout' => 1
                ]
            ];
            $context = stream_context_create($opts);
            $data = '';
            foreach ($installation_urls as $url) {
                $url = rtrim($url['installation_base_url'], "/") . "/AjaxApi/get_json_for_hpo_ancestry";
                $data = @file_get_contents($url, 1, $context);
            }
    
            if($data) {
                file_put_contents("resources/phenotype_lookup_data/" . "local_" . $network_key . "_hpo_ancestry.json", json_encode($data));
            }
    
            $phen_data = json_decode(file_get_contents("resources/phenotype_lookup_data/" . "local_" . $network_key . ".json"), 1);
            $hpo_data = json_decode(file_get_contents("resources/phenotype_lookup_data/" . "local_" . $network_key . "_hpo_ancestry.json"), 1);
            return json_encode([$phen_data, $hpo_data]);
        }
    }

    function get_json_for_phenotype_lookup() {
        $modification_time = $this->request->getVar('modification_time');
        $network_key = $this->request->getVar('network_key');

        if (file_exists('resources/phenotype_lookup_data/' . $network_key . ".json")) {
            return (file_get_contents("resources/phenotype_lookup_data/" . $network_key . ".json"));
        } else {
            error_log("resources/phenotype_lookup_data/" . $network_key . ".json");
        }              
    }

    
    function get_json_for_hpo_ancestry() {
        $modification_time = $this->request->getVar('modification_time');
        $network_key = $this->request->getVar('network_key');

        if (file_exists('resources/phenotype_lookup_data/' . $network_key . "_hpo_ancestry.json")) {
            return (file_get_contents("resources/phenotype_lookup_data/" . $network_key . "_hpo_ancestry.json"));
		}
		else {
            return false;
        }              
	}

 }