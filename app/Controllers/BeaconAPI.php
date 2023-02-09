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
use Config\Services;
use Psr\Log\LoggerInterface;
use App\Libraries\CafeVariome\Beacon\Beacon;

class BeaconAPI extends ResourceController
{

	private $setting;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
		$this->setting = CafeVariome::Settings();
    }

    public function _remap($function)
	{
		if ((int)$function > 0)
		{
			//$function is the network key
			$path = $this->request->getPath();
			$method = explode('/', $path)[2];
			if($method == "service-info")
			{
				return $this->service_info();
			}
			return $this->$method((int)$function);
		}
		else
		{
			//Bad Request, drop it with 400
			return Services::response()->setStatusCode(400, 'Bad request');
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
        $response['response']['organization']['id'] = 'ULEIC';
        $response['response']['organization']['name'] = 'University of Leicester';
        $response['response']['organization']['address'] = 'University Road, Leicester, LE1 7RH';
        $response['response']['organization']['contactUrl'] = 'mailto:admin@cafevariome.org?subject=Beacon Info';
        $response['response']['organization']['logoUrl'] = base_url('resources/images/logos/cafevariome-logo-full.png');
        $response['response']['organization']['welcomeUrl'] = 'https://le.ac.uk/health-data-research/';
        $response['response']['welcomeUrl'] = 'https://www.cafevariome.org/';
        $response['response']['alternativeUrl'] = 'https://le.ac.uk/health-data-research/activities/';
        $response['response']['organization']['description'] = 'Cafe Variome is a flexible data discovery software. Cafe Variome + Beacon makes discovering genomic data easier.';

        $result = json_encode($response);
        return $this->response->setHeader("Content-Type", "application/json")->setBody($result);
    }

    public function service_info()
    {
        $response['id'] = Beacon::GetBeaconID();
        $response['name'] = 'Cafe Variome Beacon';
        $response['type']['artifact'] = 'beacon';
        $response['type']['group'] = Beacon::GetBeaconID();
        $response['type']['version'] = Beacon::BEACON_VERSION;
        $response['organization']['name'] = 'University of Leicester';
        $response['organization']['url'] =  'https://www.le.ac.uk';
        $response['contactUrl'] = 'mailto:admin@cafevariome.org?subject=Beacon Service Info';
        $response['createdAt'] = '2021-02-03 15:07 BST';
        $response['updatedAt'] = '2022-10-06 11:56 BST';
        $response['description'] = 'This service provides information about Beacon deployed by Cafe Variome Software.';
        $response['documentationUrl'] = 'https://cafe-variome.gitbook.io/cafe-variome-docs/features/beacon/beacon-api';
        $response['environment'] = 'dev';
        $response['version'] = Beacon::BEACON_VERSION;

        $result = json_encode($response);
        return $this->response->setHeader("Content-Type", "application/json")->setBody($result);
    }

    public function configuration()
    {
        $response['meta']['beaconId'] = Beacon::GetBeaconID();
        $response['meta']['apiVersion'] = Beacon::BEACON_VERSION;
        $response['meta']['returnedSchemas'][0]['entityType'] = 'individuals';
        $response['meta']['returnedSchemas'][0]['schema'] = 'https://raw.githubusercontent.com/ga4gh-beacon/beacon-v2/main/models/json/beacon-v2-default-model/individuals/defaultSchema.json';
        $response['meta']['returnedSchemas'][1]['entityType'] = 'biosamples';
        $response['meta']['returnedSchemas'][1]['schema'] = 'https://raw.githubusercontent.com/ga4gh-beacon/beacon-v2/main/models/json/beacon-v2-default-model/biosamples/defaultSchema.json';
        $response['response']['$schema'] = "https://raw.githubusercontent.com/ga4gh-beacon/beacon-v2/main/framework/json/configuration/beaconConfigurationSchema.json";
        $response['response']['entryTypes']['Individuals']['id'] = 'Individuals';
        $response['response']['entryTypes']['Individuals']['name'] = 'Individuals';
        $response['response']['entryTypes']['Individuals']['ontologyTermForThisType']['id'] = 'NCIT:C25190';
        $response['response']['entryTypes']['Individuals']['partOfSpecification'] = Beacon::BEACON_VERSION;
        $response['response']['entryTypes']['Individuals']['defaultSchema']['id'] = 'beacon-v2-individual';
        $response['response']['entryTypes']['Individuals']['defaultSchema']['name'] = 'Default Schema for Individuals';
        $response['response']['entryTypes']['Individuals']['defaultSchema']['referenceToSchemaDefinition'] = 'https://raw.githubusercontent.com/ga4gh-beacon/beacon-v2/main/models/json/beacon-v2-default-model/individuals/defaultSchema.json';
        $response['response']['entryTypes']['Biosamples']['id'] = 'Biosamples';
        $response['response']['entryTypes']['Biosamples']['name'] = 'Biosamples';
        $response['response']['entryTypes']['Biosamples']['ontologyTermForThisType']['id'] = 'NCIT:C43412';
        $response['response']['entryTypes']['Biosamples']['partOfSpecification'] = Beacon::BEACON_VERSION;
        $response['response']['entryTypes']['Biosamples']['defaultSchema']['id'] = 'beacon-v2-biosample';
        $response['response']['entryTypes']['Biosamples']['defaultSchema']['name'] = 'Default Schema for Biosamples';
        $response['response']['entryTypes']['Biosamples']['defaultSchema']['referenceToSchemaDefinition'] = 'https://raw.githubusercontent.com/ga4gh-beacon/beacon-v2/main/models/json/beacon-v2-default-model/biosamples/defaultSchema.json';
        $response['response']['maturityAttributes']['productionStatus'] = 'DEV';
        $response['response']['securityAttributes']['defaultGranularity'] = 'count';

        $result = json_encode($response);
        return $this->response->setHeader("Content-Type", "application/json")->setBody($result);
    }

    public function entry_types()
    {
        $response['meta']['beaconId'] =  Beacon::GetBeaconID();
        $response['meta']['apiVersion'] = Beacon::BEACON_VERSION;
        $response['meta']['returnedSchemas'][0]['entityType'] = 'individuals';
        $response['meta']['returnedSchemas'][0]['schema'] = 'https://raw.githubusercontent.com/ga4gh-beacon/beacon-v2/main/models/json/beacon-v2-default-model/individuals/defaultSchema.json';
        $response['meta']['returnedSchemas'][1]['entityType'] = 'biosamples';
        $response['meta']['returnedSchemas'][1]['schema'] = 'https://raw.githubusercontent.com/ga4gh-beacon/beacon-v2/main/models/json/beacon-v2-default-model/biosamples/defaultSchema.json';$response['response']['entryTypes']['Individuals']['id'] = 'Individuals';
        $response['response']['entryTypes']['Individuals']['id'] = 'Individuals';
        $response['response']['entryTypes']['Individuals']['name'] = 'Individuals';
        $response['response']['entryTypes']['Individuals']['ontologyTermForThisType']['id'] = 'NCIT:C25190';
        $response['response']['entryTypes']['Individuals']['partOfSpecification'] = Beacon::BEACON_VERSION;
        $response['response']['entryTypes']['Individuals']['defaultSchema']['id'] = 'beacon-v2-individual';
        $response['response']['entryTypes']['Individuals']['defaultSchema']['name'] = 'Default Schema for Individuals';
        $response['response']['entryTypes']['Individuals']['defaultSchema']['referenceToSchemaDefinition'] = 'https://raw.githubusercontent.com/ga4gh-beacon/beacon-v2-Models/main/BEACON-V2-draft4-Model/individuals/defaultSchema.json';
        $response['response']['entryTypes']['Biosamples']['id'] = 'Biosamples';
        $response['response']['entryTypes']['Biosamples']['name'] = 'Biosamples';
        $response['response']['entryTypes']['Biosamples']['ontologyTermForThisType']['id'] = 'NCIT:C43412';
        $response['response']['entryTypes']['Biosamples']['partOfSpecification'] = Beacon::BEACON_VERSION;
        $response['response']['entryTypes']['Biosamples']['defaultSchema']['id'] = 'beacon-v2-biosample';
        $response['response']['entryTypes']['Biosamples']['defaultSchema']['name'] = 'Default Schema for Biosamples';
        $response['response']['entryTypes']['Biosamples']['defaultSchema']['referenceToSchemaDefinition'] = 'https://raw.githubusercontent.com/ga4gh-beacon/beacon-v2/main/models/json/beacon-v2-default-model/biosamples/defaultSchema.json';

        $result = json_encode($response);
        return $this->response->setHeader("Content-Type", "application/json")->setBody($result);
    }

    public function map()
    {
        $response['meta']['beaconId'] = Beacon::GetBeaconID();
        $response['meta']['apiVersion'] = Beacon::BEACON_VERSION;
        $response['meta']['returnedSchemas'][0]['entityType'] = 'individuals';
        $response['meta']['returnedSchemas'][0]['schema'] = 'https://raw.githubusercontent.com/ga4gh-beacon/beacon-v2/main/models/json/beacon-v2-default-model/individuals/defaultSchema.json';
        $response['meta']['returnedSchemas'][1]['entityType'] = 'biosamples';
        $response['meta']['returnedSchemas'][1]['schema'] = 'https://raw.githubusercontent.com/ga4gh-beacon/beacon-v2/main/models/json/beacon-v2-default-model/biosamples/defaultSchema.json';
        $response['response']['$schema'] = "https://raw.githubusercontent.com/ga4gh-beacon/beacon-v2/main/framework/json/configuration/beaconMapSchema.json";
        $response['response']['endpointSets']['Individuals']['entryType'] = "Individuals";
        $response['response']['endpointSets']['Individuals']['rootUrl'] = Beacon::GetIndividualsURL();
        $response['response']['endpointSets']['Biosamples']['entryType'] = 'Biosamples';
        $response['response']['endpointSets']['Biosamples']['rootUrl'] = Beacon::GetBiosamplesURL();
        $response['response']['endpointSets']['Biosamples']['filteringTermsUrl'] = base_url('resources/beacon/filtering_terms.json');

        $result = json_encode($response);
        return $this->response->setHeader("Content-Type", "application/json")->setBody($result);
    }

    public function filtering_terms()
    {
        $response['meta']['beaconId'] = Beacon::GetBeaconID();
        $response['meta']['apiVersion'] = Beacon::BEACON_VERSION;
        $response['meta']['returnedSchemas'][0]['entityType'] = 'individuals';
        $response['meta']['returnedSchemas'][0]['schema'] = 'https://raw.githubusercontent.com/ga4gh-beacon/beacon-v2/main/models/json/beacon-v2-default-model/individuals/defaultSchema.json';
        $response['meta']['returnedSchemas'][1]['entityType'] = 'biosamples';
        $response['meta']['returnedSchemas'][1]['schema'] = 'https://raw.githubusercontent.com/ga4gh-beacon/beacon-v2/main/models/json/beacon-v2-default-model/biosamples/defaultSchema.json';
        $response['response']['$schema'] = "https://raw.githubusercontent.com/ga4gh-beacon/beacon-v2/main/framework/json/configuration/filteringTermsSchema.json";
        $fterms = json_decode(file_get_contents(base_url('resources/beacon/filtering_terms.json')),true);
        foreach($fterms as $key => $val)
        {
        $response['response']['filteringTerms'][$key] = $val;
        }
        $result = json_encode($response);
        return $this->response->setHeader("Content-Type", "application/json")->setBody($result);
    }

    public function individuals(int $network_key)
    {
		$token = $this->request->header('auth-token')?->getValue();
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

			$networkInterface = new NetworkInterface();
			$installationsResponse = $networkInterface->GetInstallationsByNetworkKey($network_key); // Get other installations within this network

			$results = [$localRresults];
			if ($installationsResponse->status)
			{
				$installations = $installationsResponse->data;
				foreach ($installations as $installation)
				{
					if ($installation->installation_key != $this->setting->getInstallationKey())
					{
						// Send the query
						$queryNetInterface = new QueryNetworkInterface($installation->base_url);
						$queryResponse = $queryNetInterface->query($query_json, $network_key, $providerURL, $token);
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
						$currentCount = 0;
						if ($source['type'] == 'count')
						{
							$currentCount = $source['payload'];
						}
						else if ($source['type'] == 'list')
						{
							$currentCount = $source['count'];
						}
						$response['resultSets'][] = [
							'id' => $source_name,
							'type' => 'dataset',
							'exists' => $currentCount > 0,
							'resultCount' => $currentCount,
							'Info' => [
								'contactPoint' => $source['source']['owner_name'],
								'contactEmail' => $source['source']['owner_email'],
								'contactURL' => $source['source']['uri']
							]
						];
						$numTotalResults += $currentCount;
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
            $result = "Incorrect query";
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
            $result = "Query contains entry types not supported by this beacon for this endpoint " . json_encode($ets);
            return $this->response->setStatusCode(400)->setBody($result);
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
		$demographyQueries = [];
		$demographyCounter=0;
		$hpoSimQueries = [];
		$hpoSimCounter=0;
		$i=0;
		$showInfo = false;
		$unsupFilters = [];
		$unsupFilterValues = [];

		foreach($filterTerms as $ft)
		{
			if(
				$ft->type != "NCIT_C25150" &&
				$ft->type != "SIO_001003" &&
				$ft->type != "NCIT_C28421" &&
				$ft->type != "SIO_010056"
			)
			{
				$showInfo = true;
				array_push($unsupFilters,$ft->type);
			}
			else
			{
				$showInfo = false;
				if(property_exists($ft, 'operator'))
				{
					if(str_starts_with($ft->type, "SIO_001003"))
					{
						// ORPHA
						if(str_contains($ft->id, "_"))
						{
							$term = explode("_", $ft->id);
							$id = 'ORPHA' . ':' . $term[1];
						}
						else if (str_contains($ft->id, ":"))
						{
							$id = $ft->id;
						}
						else
						{
							$term = explode("ORPHA", $ft->id);
							$error = "Please provide ORPHA term in the right format, i.e., ORPHA" . "_" . $term[1];
							return $this->response->setStatusCode(400)->setBody($error);
						}
						array_push($ordoQueries, [
							'id' => [$id],
							'r' => 1,
							's' => 100,
							'HPO' => true
						]);
						$ordoCounter++;
					}

					if(str_starts_with($ft->type, "NCIT_C28421"))
					{
						// Gender
						if($ft->id == 'NCIT_C16576')
						{
							$genValue = "female";
						}
						elseif($ft->id == 'NCIT_C20197')
						{
							$genValue = "male";
						}
						elseif($ft->id == 'NCIT_C124294')
						{
							$genValue = "undetermined";
						}
						elseif($ft->id == 'NCIT_C17998')
						{
							$genValue = "unknown";
						}
						elseif($ft->id == '')
						{ // gender string is not sent from VP
							$genValue = "";
						}
						else
						{
							$showInfo = true;
							array_push($unsupFilterValues,$ft->id);
						}

						array_push($demographyQueries,[
							'gender' => [$genValue]
						]);
						$demographyCounter++;
					}

					if(str_starts_with($ft->type, "SIO_010056"))
					{
						// Phenotype
						array_push($hpoSimQueries,[
							'r' => 1,
							's' => $hpoSimCounter,
							'ORPHA' => false,
							'ids'=> [$ft->id]
						]);
						$hpoSimCounter++;
					}

					if(str_starts_with($ft->type, "NCIT_C25150")) // Age
					{
						if($ft->operator == '=')
						{
							// Age = Something QUERY
							array_push($demographyQueries,[
								'minAge' => [$ft->id],
								'maxAge' => [$ft->id],
							]);
						}
						elseif($ft->operator == '>=')
						{
							array_push($demographyQueries,[
								'minAge' => [$ft->id],
								'maxAge' => [99],
							]);
						}
						elseif($ft->operator == '<=')
						{
							array_push($demographyQueries,[
								'minAge' => [0],
								'maxAge' => [$ft->id],
							]);
						}
						elseif($ft->operator == '>')
						{
							array_push($demographyQueries,[
								'minAge' => [$ft->id + 1],
								'maxAge' => [99],
							]);
						}
						elseif($ft->operator == '<')
						{
							array_push($demographyQueries,[
								'minAge' => [0],
								'maxAge' => [$ft->id - 1],
							]);
						}
						$demographyCounter++;
					}
				}
				if(property_exists($ft, 'operator'))
				{
					if(
						!(str_starts_with($ft->type, "NCIT_C25150")) && // Not age
						!(str_starts_with($ft->type, "SIO_001003")) && // Not diagnosis
						!(str_starts_with($ft->type, "NCIT_C28421")) && // Not gender
						!(str_starts_with($ft->type, "SIO_010056")) && // Not phenotype
						!(str_starts_with($ft->type, "NCIT_C16612")) // Not causative gene
					) // EAV queries
					{
						array_push($eavQueries, [
							'attribute' => strtolower($ft->type),
							'operator' => $ft->operator,
							'value' => strtolower($ft->id)
						]);
						$eavCounter++;
					}
				}
			}
			$i++;
		}

		for ($j = 0; $j < $eavCounter; $j++)
		{
			$qArr['logic']['-AND'][] = "/query/components/eav/" . $j;
		}
		for ($j = 0; $j < $ordoCounter; $j++)
		{
			$qArr['logic']['-AND'][] = "/query/components/ordo/" . $j;
		}
		for ($j = 0; $j < $demographyCounter; $j++)
		{
			$qArr['logic']['-AND'][] = "/query/components/demography/0/".$j; // single demography query
		}
		for ($j = 0; $j < $hpoSimCounter; $j++)
		{
			$qArr['logic']['-AND'][] = "/query/components/sim/" . $j; // multiple hpo queries
		}

		$qArr['query']['components']['eav'] = $eavQueries;
		$qArr['query']['components']['ordo'] = $ordoQueries;
		$qArr['query']['components']['demography'][0]= $demographyQueries;
		$qArr['query']['components']['sim']= $hpoSimQueries;
		$qArr['requires']['response']['components'] = [];

		$query_json = json_encode($qArr, JSON_UNESCAPED_SLASHES);

		$localRresults = $queryCompiler->CompileAndRunQuery($query_json, $network_key, $user_id);

		$networkInterface = new NetworkInterface();
		$response = $networkInterface->GetInstallationsByNetworkKey($network_key); // Get other installations within this network
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
					$queryResponse = $queryNetInterface->query($query_json, $network_key, $token);
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
		$response['meta']['receivedRequestSummary'] = $json;
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
					$currentCount = 0;
					if ($source['type'] == 'count')
					{
						$currentCount = $source['payload'];
					}
					else if ($source['type'] == 'list')
					{
						$currentCount = $source['count'];
					}
					$response['resultSets'][] = [
						'id' => $source_name,
						'type' => 'dataset',
						'exists' => $currentCount > 0,
						'resultCount' => $currentCount,
						'Info' => [
							'contactPoint' => $source['source']['owner_name'],
							'contactEmail' => $source['source']['owner_email'],
							'contactURL' => $source['source']['uri']
						]
					];
					$numTotalResults += $currentCount;
				}
			}
		}

		if($showInfo)
		{
			$response['responseSummary']['numTotalResults'] = $numTotalResults;
			$response['responseSummary']['exists'] = $numTotalResults > 0;
			$response['info']['warnings']['unsupportedFilters'] = $unsupFilters;

			if(count($unsupFilterValues)>0)
			{
				$response['info']['warnings']['unsupportedFilterValues'] = $unsupFilterValues;
			}
		}
		else
		{
			$response['responseSummary']['numTotalResults'] = $numTotalResults;
			$response['responseSummary']['exists'] = $numTotalResults > 0;
		}

		$result = json_encode($response);

		return $this->response->setHeader("Content-Type", "application/json")->setBody($result);

    }


}
