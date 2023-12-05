<?php namespace App\Controllers;

/**
 * QueryAPI.php
 *
 * Created : 27/01/2020
 *
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 * @author Sadegh Abadijou
 *
 * This controller contains RESTful listeners for network operations.
 * Most methods are ported from netauth.php in the previous version.
 */

use App\Libraries\CafeVariome\Core\DataPipeLine\Index\UserInterfaceNetworkIndex;
use App\Libraries\CafeVariome\Factory\AuthenticatorFactory;
use App\Libraries\CafeVariome\Factory\SingleSignOnProviderAdapterFactory;
use App\Libraries\CafeVariome\Helpers\Core\URLHelper;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Libraries\CafeVariome\Core\APIResponseBundle;
use App\Libraries\CafeVariome\Core\IO\FileSystem\SysFileMan;


class QueryAPI extends ResourceController
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
        $networkKey = $this->request->getVar('network_key');
        $queryString = $this->request->getVar('query');
        $token = json_decode($this->request->getVar('token'), true);
		$providerURL = $this->request->getVar('authentication_url');
		$this->providerID =  (new SingleSignOnProviderAdapterFactory())->GetInstance()->ReadIDbyURL(str_replace(':' .
			URLHelper::ExtractPort($providerURL), '', $providerURL));

		$apiResponseBundle = new APIResponseBundle();

		if ($providerURL != null)
		{
			$providerURL = str_replace(':' . URLHelper::ExtractPort($providerURL), '', $providerURL); // Extract and remove port, if it exists
			$singleSignOnProvider = (new SingleSignOnProviderAdapterFactory())->GetInstance()->ReadByURL($providerURL);


			if (!$singleSignOnProvider->isNull())
			{
				if ($token != null)
				{
					$authenticator = (new AuthenticatorFactory())->GetInstance($singleSignOnProvider);
					$user_id = $authenticator->GetUserIdByToken($token);
					$cafeVariomeQuery = new \App\Libraries\CafeVariome\Query\Compiler($this->providerID);
					try
					{
						$resp = $cafeVariomeQuery->CompileAndRunQuery($queryString, $networkKey, $user_id);
						$apiResponseBundle->initiateResponse(1, json_decode($resp, true));
					}
					catch (\Exception $ex)
					{
						error_log($ex->getMessage());
						$apiResponseBundle->initiateResponse(0);
						$apiResponseBundle->setResponseMessage($ex->getMessage());
					}
				}
				else
				{
					//No token present with query, unauthorised
					$apiResponseBundle->initiateResponse(0);
					$apiResponseBundle->setResponseMessage('Token not found. Unauthorised query.');
				}
			}
			else
			{
				// Authentication provider not found
				$apiResponseBundle->initiateResponse(0);
				$apiResponseBundle->setResponseMessage('Authentication provider not found. Unauthorised query.');
			}
		}
		else
		{
			// Authentication provider URL not present
			$apiResponseBundle->initiateResponse(0);
			$apiResponseBundle->setResponseMessage('Authentication provider URL not present. Unauthorised query.');
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
        $basePath = FCPATH . USER_INTERFACE_INDEX_DIR;

        $modification_time = $this->request->getVar('modification_time');
        $network_key = $this->request->getVar('network_key');

        $apiResponseBundle = new APIResponseBundle();
		$userInterfaceNetworkIndex = new UserInterfaceNetworkIndex($network_key, $this->providerID);

        try {
            $resp = [];
            $fileMan = new SysFileMan($basePath);

            $resp['modified'] = false;
            $resp['json'] = '';

			if(!$fileMan->Exists($network_key . '.json') || $userInterfaceNetworkIndex->SourceIndicesUpdated()){
				// Create network index
				$userInterfaceNetworkIndex->IndexNetwork();
				$resp['json'] = $fileMan->Read($network_key . '.json');
				$resp['modified'] = true;
			}
			else if($fileMan->GetModificationTimeStamp($network_key . '.json') > $modification_time)
			{
				$resp['json'] = $fileMan->Read($network_key . '.json');
				$resp['modified'] = true;
			}

            $apiResponseBundle->initiateResponse(1, $resp);
        }
		catch (\Exception $ex) {
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
