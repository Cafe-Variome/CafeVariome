<?php namespace App\Controllers;

/**
 * BeaconAPI.php
 *
 * Created : 17/02/2021
 *
 * @author Colin Veal
 * @author Mehdi Mehtarizadeh
 * @author Vatsalya Maddi
*/

use App\Libraries\CafeVariome\CafeVariome;
use App\Libraries\CafeVariome\Factory\AuthenticatorFactory;
use App\Libraries\CafeVariome\Factory\SingleSignOnProviderAdapterFactory;
use App\Libraries\CafeVariome\Helpers\Core\URLHelper;
use App\Libraries\CafeVariome\Net\NetworkInterface;
use App\Libraries\CafeVariome\Net\QueryNetworkInterface;
use App\Libraries\CafeVariome\Query\Compiler;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Libraries\CafeVariome\Beacon\Beacon;

class BeaconAPI extends ResourceController
{

	private $setting;

	private $beacon;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
		$this->setting = CafeVariome::Settings();
    }

    public function _remap($function)
	{
        if($function == "service-info"){
            return $this->service_info();
        }
        else{
            return $this->$function();
        }
    }

    public function Index()
	{
        return redirect()->to(base_url('BeaconAPI/info'));
    }

    public function info()
    {
        $response['meta']['beaconId'] = Beacon::GetBeaconID();
        $response['meta']['apiVersion'] = Beacon::BEACON_VERSION;
        $response['meta']['returnedSchemas']['entityType'] = 'Info Endpoint';
        $response['meta']['returnedSchemas']['schema'] = 'https://raw.githubusercontent.com/ga4gh-beacon/beacon-v2/main/models/json/beacon-v2-default-model/endpoints.json';
        $response['response']['id'] = Beacon::GetBeaconID();
        $response['response']['name'] = 'Cafe Variome Beacon';
        $response['response']['apiVersion'] = Beacon::BEACON_VERSION;
        $response['response']['createDateTime'] = "2021-02-03 15:07 BST";
        $response['response']['updateDateTime'] = "2022-10-05 17:18 BST";
        $response['response']['description'] = "This Beacon is based on the Beacon specification by GA4GH. Implemented by The Brookeslab @ University of Leicester, this Beacon contains all informational endpoints along with individuals and biosamples discovery.";
        $response['response']['environment'] = "dev";
        $response['response']['organisation']['id'] = 'ULEIC';
        $response['response']['organisation']['name'] = 'University of Leicester';
        $response['response']['organisation']['address'] = 'University Road, Leicester, LE1 7RH';
        $response['response']['organisation']['contactUrl'] = 'mailto:admin@cafevariome.org?subject=Beacon Info';
        $response['response']['organisation']['logoUrl'] = base_url('resources/images/logos/cafevariome-logo-full.png');
        $response['response']['organisation']['welcomeUrl'] = 'https://le.ac.uk/health-data-research/';
        $response['response']['welcomeUrl'] = 'https://www.cafevariome.org/';
        $response['response']['alternativeUrl'] = 'https://le.ac.uk/health-data-research/activities/';
        $response['response']['organisation']['description'] = 'Cafe Variome is a flexible data discovery software. Cafe Variome + Beacon makes discovering genomic data easier.';
    
        $result = json_encode($response);
        return $this->response->setHeader("Content-Type", "application/json")->setBody($result);
    }

    public function service_info()
    {
        $response['id'] = Beacon::GetBeaconID();
        $response['name'] = 'CafeVariome beacon';
        $response['type']['group'] = Beacon::GetBeaconID();
        $response['type']['artifact'] = 'beacon';
        $response['type']['version'] = Beacon::BEACON_VERSION;
        $response['organization']['name'] = 'University of Leicester';
        $response['organization']['url'] =  'https://www.le.ac.uk';
        $response['version'] = '2.0';

        $result = json_encode($response);
        return $this->response->setHeader("Content-Type", "application/json")->setBody($result);
    }

    public function configuration()
    {
        $response['$schema'] = "../beaconConfigurationResponse.json";
        $response['meta']['beaconId'] = Beacon::GetBeaconID();
        $response['meta']['apiVersion'] = Beacon::BEACON_VERSION;
        $response['meta']['returnedSchemas'][0]['entityType'] = 'Individuals';
        $response['meta']['returnedSchemas'][0]['schema'] = 'string';
        $response['response']['$schema'] = "https://raw.githubusercontent.com/ga4gh-beacon/beacon-framework-v2/main/configuration/beaconConfigurationSchema.json";
        $response['response']['maturityAttributes']['productionStatus'] = 'DEV';
        $response['response']['entryTypes']['Individuals']['id'] = 'Individuals';
        $response['response']['entryTypes']['Individuals']['name'] = 'Individuals';
        $response['response']['entryTypes']['Individuals']['ontologyTermForThisType']['id'] = 'NCIT:C25190';
        $response['response']['entryTypes']['Individuals']['partOfSpecification'] = 'Beacon v2.0-draft3';
        $response['response']['entryTypes']['Individuals']['defaultSchema']['id'] = 'ga4gh-beacon-individual-v2.0.0-draft.4';
        $response['response']['entryTypes']['Individuals']['defaultSchema']['name'] = 'Default Schema for Individuals';
        $response['response']['entryTypes']['Individuals']['defaultSchema']['referenceToSchemaDefinition'] = 'https://raw.githubusercontent.com/ga4gh-beacon/beacon-v2-Models/blob/main/BEACON-V2-draft4-Model/individuals/defaultSchema.json';

        $result = json_encode($response);
        return $this->response->setHeader("Content-Type", "application/json")->setBody($result);
    }

    public function entry_types()
    {

        $response['meta']['beaconId'] =  Beacon::GetBeaconID();
        $response['meta']['apiVersion'] = Beacon::BEACON_VERSION;
        $response['meta']['returnedSchemas'][0]['entityType'] = 'Individual';
        $response['meta']['returnedSchemas'][0]['schema'] = 'string';
        $response['response']['entryTypes']['Individuals']['id'] = 'Individuals';
        $response['response']['entryTypes']['Individuals']['name'] = 'Individuals';
        $response['response']['entryTypes']['Individuals']['ontologyTermForThisType']['id'] = 'NCIT:C25190';
        $response['response']['entryTypes']['Individuals']['partOfSpecification'] = 'Beacon v2.0-draft3';
        $response['response']['entryTypes']['Individuals']['defaultSchema']['id'] = 'ga4gh-beacon-individual-v2.0.0-draft.4';
        $response['response']['entryTypes']['Individuals']['defaultSchema']['name'] = 'Default Schema for Individuals';
        $response['response']['entryTypes']['Individuals']['defaultSchema']['referenceToSchemaDefinition'] = 'https://raw.githubusercontent.com/ga4gh-beacon/beacon-v2-Models/main/BEACON-V2-draft4-Model/individuals/defaultSchema.json';

        $result = json_encode($response);
        return $this->response->setHeader("Content-Type", "application/json")->setBody($result);
    }

    public function map()
    {
        $response['meta']['beaconId'] = Beacon::GetBeaconID();
        $response['meta']['apiVersion'] = Beacon::BEACON_VERSION;
        $response['meta']['returnedSchemas'][0]['entityType'] = 'Individual';
        $response['meta']['returnedSchemas'][0]['schema'] = 'string';
        $response['response']['$schema'] = "string";
        $response['response']['endpointSets']['Individuals']['entryType'] = "Individuals";
        $response['response']['endpointSets']['Individuals']['rootUrl'] = Beacon::GetIndividualsURL();

        $result = json_encode($response);
        return $this->response->setHeader("Content-Type", "application/json")->setBody($result);
    }

    public function individuals()
    {
		$token = $this->request->header('auth-token')?->getValue();
		$network_key = $this->request->header('network-key')?->getValue();
		$providerURL = $this->request->header('authentication-url')?->getValue();

		if (
			$token == null ||
			$token == '' ||
			$providerURL == null ||
			$providerURL = ''
		)
		{
			// If no token is specified then drop the request with a 400.
			$result = "This is a secure Beacon API. Please include a valid authentication token along with an authentication URL.";
			return $this->response->setStatusCode(403)->setBody($result);
		}

		if (
			$network_key == null ||
			$network_key == ''
		)
		{
			// If no network key is specified then drop the request with a 403.
			$result = "Please specify the network key you want to discover on.";
			return $this->response->setStatusCode(400)->setBody($result);
		}

		$providerURL = str_replace(URLHelper::ExtractPort($providerURL), '', $providerURL); // Extract and remove port, if it exists
		$singleSignOnProvider = (new SingleSignOnProviderAdapterFactory())->GetInstance()->ReadByURL($providerURL);

		if (!$singleSignOnProvider->isNull())
		{
			$result = "The authentication URL provided has not been authorized on for use on this Beacon server.";
			return $this->response->setStatusCode(403)->setBody($result);
		}

		$authenticator = (new AuthenticatorFactory())->GetInstance($singleSignOnProvider);
		$user_id = $authenticator->GetUserIdByToken($token);

		$queryCompiler = new Compiler();

        $eavQueries = [];
        $diseaseCodes = [];

        $json = $this->request->getJSON(true);

        if (
			$json == null ||
			count($json) == 0 ||
			count($json['query']) == 0
		)
		{
			// A matchAll query must be run to collect all matching subjects
            $response['meta']['beaconId'] = Beacon::GetBeaconID();
            $response['meta']['apiVersion'] = Beacon::BEACON_VERSION;
            $response['meta']['receivedRequest'] = $json;
            $response['meta']['returnedSchemas'][]['entityType'] = "Individuals";
            $response['meta']['returnedSchemas'][]['schema'] = "ga4gh-beacon-individual-v2.0.0-draft.4";
            $response['meta']['returnedGranularity']='count';
            $response['meta']['receivedRequestSummary']['apiVersion'] = Beacon::BEACON_VERSION;
            $response['meta']['receivedRequestSummary']['requestedSchemas'][]['entityType'] = 'Individual';
            $response['meta']['receivedRequestSummary']['requestedSchemas'][]['schema'] = 'ga4gh-beacon-individual-v2.0.0-draft.4';
            $response['meta']['receivedRequestSummary']['includeResultsetResponses'] = 'HIT';
            $response['meta']['receivedRequestSummary']['pagination']['skip'] = 0;
            $response['meta']['receivedRequestSummary']['pagination']['limit'] = 10;
            $response['meta']['receivedRequestSummary']['requestedGranularity'] = 'count';
			$response['response']['resultSets'] = array();

			$qArr = [];
			$qArr['query']['components']['matchAll'][0] = [];
			$qArr['requires']['response']['components'] = [];

			$query_json = json_encode($qArr, JSON_UNESCAPED_SLASHES);
			$localRresults = $queryCompiler->CompileAndRunQuery($query_json, $network_key, $user_id);

			$results = [$localRresults];
			if ($response->status)
			{
				$installations = $response->data;
				foreach ($installations as $installation)
				{
					if ($installation->installation_key != $this->setting->getInstallationKey())
					{
						// Send the query
						$queryNetInterface = new QueryNetworkInterface($installation->base_url);
						$queryResponse = $queryNetInterface->query($query_json, (int) $network_key, $token);
						if ($queryResponse->status)
						{
							array_push($results, json_encode($queryResponse->data));
						}
					}
				}
			}

			$numTotalResults = 0;
			foreach ($results as $sourceJsonString)
			{
				$sourceArray = json_decode($sourceJsonString, true);
				if(count($sourceArray)> 0)
				{
					foreach ($sourceArray as $source_name => $source)
					{
						$response['resultSets'][] = [
							'id' => $source_name,
							'type' => 'dataset',
							'exists' => count($source['records']['subjects']) > 0,
							'resultCount' => count($source['records']['subjects']),
							'Info' => [
								'contactPoint' => $source['details']['owner_name'],
								'contactEmail' => $source['details']['email'],
								'contactURL' => $source['details']['uri']
							]
						];
						$numTotalResults += count($source['records']['subjects']) ;
					}
				}
			}

			$response['responseSummary']['exists'] = $numTotalResults > 0;
			$response['responseSummary']['numTotalResults'] = $numTotalResults;
            $result = json_encode($response);

            return $this->response->setHeader("Content-Type", "application/json")->setBody($result);
        }

        if (
			!in_array('query',array_map('strtolower',array_keys($json))) ||
			!in_array('meta',array_map('strtolower',array_keys($json)))
		)
		{
            $result = "incorrect query";
            return $this->response->setStatusCode(400)->setBody($result);
        }

        $supportedEntryTypes = ['individuals', 'g_variants', 'biosamples'];
        $ets = [];
        if (array_key_exists('requestParameters', $json['query']))
		{
            $ets = array_diff(array_map('strtolower',array_keys($json['query']['requestParameters'])), $supportedEntryTypes);
        }
        if (count($ets)>0)
		{
            $result = "query contains entry types not supported by this beacon for this endpoint " . json_encode($ets);
            return $this->response->setStatusCode(400)->setBody($result);
        }

        // check if each element supported and then package in a way to create a standard CV query
        foreach ($json['query'] as $qe => $qel)
		{
            if ($qe == 'requestParameters')
			{
                foreach ($json['query']['requestParameters'] as $et => $par )
				{
                    if ($et == 'individuals')
					{
                        foreach($par as $qel => $val)
						{
                            if ($qel == 'sex')
							{
                                array_push($eavQueries, ['attribute' => 'gender','operator' => 'eq', 'value' => $val['id']]);
                            }
							elseif ($qel == 'diseases')
							{
                                foreach ($val as $dis)
								{
                                    //check other keys if not diseaseCode then fail
                                    $unsupInd = [];
                                    foreach ($dis as $del => $dval)
									{
                                        if ($del == 'diseaseCode')
										{
                                            array_push($diseaseCodes, $dval['id']);
                                        }
										else
										{
                                            // add an array to collect unsupported elements and single reply of all problems
                                            array_push($unsupInd, $del);
                                        }
                                    }
                                    if (!empty($unsupInd))
									{
                                        $result = "query contains parameters not supported by this beacon for the Individuals/diseases endpoint " . json_encode($unsupInd);
                                        return $this->response->setStatusCode(400)->setBody($result);
                                    }
                                }
                            }
							elseif ($qel == 'phenotypicFeatures')
							{
                                //get ids and excluded or not
                            }
							else
							{
                                $result = "query contains parameters not supported by this beacon for the Individuals endpoint " . json_encode($qel);
                                return $this->response->setStatusCode(400)->setBody($result);
                            }
                        }
                    }
					elseif ($et == 'g_variants')
					{

                    }
					elseif ($et == 'biosamples')
					{

                    }
					else
					{
                        $result = "query contains entry types not supported by this beacon for this endpoint " . json_encode($et);
                        return $this->response->setStatusCode(400)->setBody($result);
                    }
                }
            }
        }

        $beaconInput = $this->request->getJSON();

        $query = $beaconInput->query;
        $filters = $query->filters;
        $filterTerms = $filters;

        $qArr = [];
        $eavQueries = [];
        $eavCounter = 0;
        $ordoQueries = [];
        $ordoCounter = 0;
        $i=0;

        foreach($filterTerms as $ft){
            if(property_exists($ft, 'operator')){
                	array_push($eavQueries, [
                    	'attribute' => strtolower($ft->id),
                    	'operator' => $ft->operator,
                    	'value' => strtolower($ft->value)
                	]);
                $eavCounter++;
            }
			elseif(str_starts_with($ft->id, "ORPHA")){
                array_push($ordoQueries, [
                    'id' => [$ft->id],
                    'r' => 1,
                    's' => 100,
                    'HPO' => true
                ]);
                $ordoCounter++;
            }
            $i++;
        }

        for ($j=0; $j < $eavCounter; $j++) {
            $qArr['logic']['-AND'][] = "/query/components/eav/" . $j;
        }
        for ($j=0; $j < $ordoCounter; $j++) {
            $qArr['logic']['-AND'][] = "/query/components/ordo/" . $j;
        }
        $qArr['query']['components']['eav'] = $eavQueries;
        $qArr['query']['components']['ordo'] = $ordoQueries;

        $qArr['requires']['response']['components'] = [];

		// Run Query locally
        $query_json = json_encode($qArr, JSON_UNESCAPED_SLASHES);

		$localRresults = $queryCompiler->CompileAndRunQuery($query_json, $network_key, $user_id);

		$networkInterface = new NetworkInterface();

		$response = $networkInterface->GetInstallationsByNetworkKey((int)$network_key); // Get other installations within this network

		$results = [$localRresults];
		if ($response->status)
		{
			$installations = $response->data;
			foreach ($installations as $installation)
			{
				if ($installation->installation_key != $this->setting->getInstallationKey())
				{
					// Send the query
					$queryNetInterface = new QueryNetworkInterface($installation->base_url);
					$queryResponse = $queryNetInterface->query($query_json, (int) $network_key, $token);
					if ($queryResponse->status)
					{
						array_push($results, json_encode($queryResponse->data));
					}
				}
			}
		}

		$response = [];
		$response['meta']['beaconId'] = Beacon::GetBeaconID();
		$response['meta']['apiVersion'] = Beacon::BEACON_VERSION;
        $response['meta']['receivedRequest'] = $json;
        $response['meta']['returnedSchemas'][]['entityType'] = "Individuals";
        $response['meta']['returnedSchemas'][]['schema'] = "ga4gh-beacon-individual-v2.0.0-draft.4";
        $response['meta']['returnedGranularity'] = 'count';
        $response['resultSets'] = [];

		$numTotalResults = 0;
		foreach ($results as $sourceJsonString)
		{
			$sourceArray = json_decode($sourceJsonString, true);
			if(count($sourceArray)> 0)
			{
				foreach ($sourceArray as $source_name => $source)
				{
					$response['resultSets'][] = [
						'id' => $source_name,
						'type' => 'dataset',
						'exists' => count($source['records']['subjects']) > 0,
						'resultCount' => count($source['records']['subjects']),
						'Info' => [
							'contactPoint' => $source['details']['owner_name'],
							'contactEmail' => $source['details']['email'],
							'contactURL' => $source['details']['uri']
						]
					];
					$numTotalResults += count($source['records']['subjects']) ;
				}
			}
		}

		$response['responseSummary']['numTotalResults'] = $numTotalResults;
		$response['responseSummary']['exists'] = $numTotalResults > 0;

        $result = json_encode($response);

        return $this->response->setHeader("Content-Type", "application/json")->setBody($result);
    }


}
