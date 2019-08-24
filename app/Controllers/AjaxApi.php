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
        
        /*
        $url = 'https://www194.lamp.le.ac.uk/phenopackets_demo/discovery/search';
        $ch = curl_init($url);
        $payload = $api;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resp['Phenopackets'] = curl_exec($ch);
        curl_close($ch);
        */
        $cafeVariomeQuery = new \App\Libraries\CafeVariome\Query();

        $resp['Phenopackets'] = $cafeVariomeQuery->search($api, $network_key);
        header('Content-Type: application/json');
        echo json_encode($resp);
    }

 }