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
 * 
 */

use CodeIgniter\Controller;


 class AjaxApi extends Controller{

    /**
	 * Constructor
	 *
	 */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger){
        parent::initController($request, $response, $logger);

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
        } else {
            return file_get_contents("https://www240.lamp.le.ac.uk/hpo/query.php");
        }
    }

    function build_tree() {

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