<?php namespace App\Controllers;

/**
 * NetworkApi.php
 * 
 * Created : 01/10/2019
 * 
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 * 
 * This controller contains RESTful listeners for network operations. 
 * Most methods are ported from netauth.php in the previous version.
 */

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

use App\Models\NetworkRequest;
use App\Libraries\CafeVariome\Core\APIResponseBundle;

class NetworkApi extends ResourceController{

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
 
        $this->validateRequest();
    }

    private function validateRequest()
    {
        
    }

    public function requestToJoinNetwork()
    {
        $network_key = $this->request->getVar('network_key');
        $installation_key = $this->request->getVar('installation_key');
        $email = $this->request->getVar('email');
        $justification = $this->request->getVar('justification');
        $ip = $this->request->getVar('ip_address');
        $token =  $this->request->getVar('token');
        $url =  $this->request->getVar('url');

        $networkRequest = [
            'network_key' => $network_key,
            'installation_key' => $installation_key,
            'email' => $email,
            'justification' => $justification,
            'ip' => $ip,
            'token' => $token,
            'url' => $url,
            'status' => -1 // Indicates a pending request
        ];

        $apiResponseBundle = new APIResponseBundle();
        $networkRequestModel = new NetworkRequest();

        try {
            $networkRequestModel->createNetworkRequest($networkRequest);
            $apiResponseBundle->initiateResponse(1);
            
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
            $apiResponseBundle->initiateResponse(0);
            $apiResponseBundle->setResponseMessage($ex->getMessage());
        }


        return $this->respond($apiResponseBundle->getResponseJSON());
    }

}