<?php namespace App\Controllers;

use App\Libraries\CafeVariome\Core\IO\FileSystem\SysFileMan;
use App\Libraries\CafeVariome\Net\HPONetworkInterface;

/**
 * ContentAPI.php
 *
 * Created 25/08/2021
 *
 * @author Mehdi Mehtraizadeh
 * @author Gregory Warren
 *
 * This controller contains endpoints that serve (static) content.
 */

class ContentAPI extends BaseController
{
	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		parent::initController($request, $response, $logger);
	}

	public function hpoQuery(string $hpo_term = ''){

		$hpoNetworkInterface = new HPONetworkInterface();
		$results = $hpoNetworkInterface->getHPO($hpo_term);
		return json_encode($results);
	}

	public function buildHPOTree() {
		if ($this->request->isAJAX())
		{
			$hpoNetworkInterface = new HPONetworkInterface();

			$hpo_json = json_decode(stripslashes($this->request->getVar('hpo_json')), 1);
			$hpo_json = json_decode(str_replace('"true"', 'true', json_encode($hpo_json)), 1);
			$hpo_json = json_decode(str_replace('"false"', 'false', json_encode($hpo_json)), 1);

			$ancestry = $this->request->getVar('ancestry');
			$hp_term = explode(' ', $this->request->getVar('hp_term'))[0];

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
									$dat = json_encode($hpoNetworkInterface->getHPO($str));
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

	public function loadOrpha(){
		$this->response->setHeader("Content-Type", "application/json");

		$path = FCPATH . RESOURCES_DIR . STATIC_DIR;
		$fileMan = new SysFileMan($path);
		$terms = [];
		if ($fileMan->Exists(ORPHATERMS_SOURCE)) {
			while (($line = $fileMan->ReadLine(ORPHATERMS_SOURCE)) != false) {
				$termPair = $line;
				$termPair = str_replace('"', "", $termPair);
				$termPair = str_replace("\n", "", $termPair);
				$termPairArr = explode(",", $termPair);

				array_push($terms, $termPairArr[0] . ' ' . $termPairArr[1]);
			}
		}

		return json_encode($terms);
	}
}
