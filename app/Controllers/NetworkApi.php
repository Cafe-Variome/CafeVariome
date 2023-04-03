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
 */

use App\Libraries\CafeVariome\Factory\NetworkRequestAdapterFactory;
use App\Libraries\CafeVariome\Factory\NetworkRequestFactory;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
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

        $apiResponseBundle = new APIResponseBundle();
		$networkRequestAdapter = (new NetworkRequestAdapterFactory())->GetInstance();
		$networkRequest = (new NetworkRequestFactory())->GetInstanceFromParameters(
			$network_key, $installation_key, $url, $justification, $email, $ip, $token, NETWORKREQUEST_PENDING
		);

        try
		{
			$networkRequestAdapter->Create($networkRequest);
			$apiResponseBundle->initiateResponse(1);
        }
		catch (\Exception $ex)
		{
            $apiResponseBundle->initiateResponse(0);
            $apiResponseBundle->setResponseMessage($ex->getMessage());
        }

        return $this->respond($apiResponseBundle->getResponseJSON());
    }
}
