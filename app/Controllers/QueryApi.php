<?php namespace App\Controllers;

/**
 * QueryApi.php
 *
 * Created : 27/01/2020
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

use App\Libraries\CafeVariome\Core\APIResponseBundle;
use App\Libraries\CafeVariome\Auth\AuthAdapter;
use App\Libraries\CafeVariome\Core\IO\FileSystem\SysFileMan;
use CodeIgniter\Config\Services;


class QueryApi extends ResourceController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->validateRequest();
    }

    private function validateRequest()
    {

    }

    public function Query()
    {
        $network_key = $this->request->getVar('network_key');
        $queryString = $this->request->getVar('query');
        //$user_id = $this->request->getVar('user_id');
        $token = json_decode($this->request->getVar('token'), true);
		$apiResponseBundle = new APIResponseBundle();

        if ($token != null) {

            $token = new \League\OAuth2\Client\Token\AccessToken($token);
            $authAdapterConfig = config('AuthAdapter');
            $authAdapter = new AuthAdapter($authAdapterConfig->authRoutine);
            $user_id = $authAdapter->getUserIdByToken($token);

            $cafeVariomeQuery = new \App\Libraries\CafeVariome\Query\Compiler();

            $networkRequestModel = new NetworkRequest();
            $resp = [];
            try {
                $resp = $cafeVariomeQuery->CompileAndRunQuery($queryString, $network_key, $user_id);
                $apiResponseBundle->initiateResponse(1, json_decode($resp, true));

            } catch (\Exception $ex) {
                error_log($ex->getMessage());
                $apiResponseBundle->initiateResponse(0);
                $apiResponseBundle->setResponseMessage($ex->getMessage());
            }
        }
        else{
            //No token present with query, unauthorised
            $apiResponseBundle->initiateResponse(0);
            $apiResponseBundle->setResponseMessage('Token not found. Unauthorised query.');
        }
        return $this->respond($apiResponseBundle->getResponseJSON());
    }

    public function getJSONDataModificationTime()
    {
        $network_key = $this->request->getVar('network_key');
        $checksum = $this->request->getVar('checksum');
        $ishpo = (bool)$this->request->getVar('ishpo');
        $loadFile = (bool)$this->request->getVar('loadfile');

        $apiResponseBundle = new APIResponseBundle();

        try {
            $resp = [];
            $file = FCPATH. DIRECTORY_SEPARATOR . JSON_DATA_DIR . $network_key . (($ishpo) ? '_hpo_ancestry.json' : ".json");
            $resp['checksum'] = '';
            if (file_exists($file)) {
                $new_checksum = sha1_file($file);
                if ($new_checksum != $checksum) {
                    $resp['checksum'] = $new_checksum;
                    if ($loadFile) {
                        $resp['file'] = file_get_contents($file);
                    }
                }
            }
            $apiResponseBundle->initiateResponse(1, $resp);

        } catch (\Exception $ex) {
            error_log($ex->getMessage());
            $apiResponseBundle->initiateResponse(0);
            $apiResponseBundle->setResponseMessage($ex->getMessage());
        }

        return $this->respond($apiResponseBundle->getResponseJSON());
    }

    public function getEAVJSON()
    {
        $basePath = FCPATH . JSON_DATA_DIR;

        $modification_time = $this->request->getVar('modification_time');
        $network_key = $this->request->getVar('network_key');

        $apiResponseBundle = new APIResponseBundle();

        try {
            $resp = [];
            $fileMan = new SysFileMan($basePath);

            $resp['modified'] = false;
            $resp['json'] = '';
            if ($fileMan->Exists($network_key . ".json")) {
            	if ($fileMan->GetModificationTimeStamp($network_key . ".json") > $modification_time){
					$resp['json'] = $fileMan->Read($network_key . ".json");
					$resp['modified'] = true;
				}
            }
            else {
                $resp['json'] = false;
            }

            $apiResponseBundle->initiateResponse(1, $resp);
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
            $apiResponseBundle->initiateResponse(0);
            $apiResponseBundle->setResponseMessage($ex->getMessage());
        }

        return $this->respond($apiResponseBundle->getResponseJSON());
    }

    public function getHPOJSON()
    {
        $basePath = FCPATH . JSON_DATA_DIR;

        $modification_time = $this->request->getVar('modification_time');
        $network_key = $this->request->getVar('network_key');

        $apiResponseBundle = new APIResponseBundle();

        try {
            $resp = [];
            $fileMan = new SysFileMan($basePath);

			$resp['modified'] = false;
			$resp['json'] = '';
            if ($fileMan->Exists($network_key . "_hpo_ancestry.json")) {
				if ($fileMan->GetModificationTimeStamp($network_key . "_hpo_ancestry.json") < $modification_time) {
					$resp['json'] = $fileMan->Read($network_key . "_hpo_ancestry.json");
					$resp['modified'] = true;
				}
			}
            else {
                $resp['json'] = false;
            }

            $apiResponseBundle->initiateResponse(1, $resp);
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
            $apiResponseBundle->initiateResponse(0);
            $apiResponseBundle->setResponseMessage($ex->getMessage());
        }

        return $this->respond($apiResponseBundle->getResponseJSON());
    }
}
