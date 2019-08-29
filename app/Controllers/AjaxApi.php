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

 class AjaxApi extends CVUI_Controller{

    /**
	 * Constructor
	 *
	 */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger){
        parent::setAuthLevel(false, false);
        parent::initController($request, $response, $logger);

    }

    function query($network_key = '') {
        $api = json_encode($this->request->getVar('jsonAPI'));

        $cafeVariomeQuery = new \App\Libraries\CafeVariome\Query();
        $resp['Phenopackets'] = $cafeVariomeQuery->search($api, $network_key);

        return json_encode($resp);
    }

 }