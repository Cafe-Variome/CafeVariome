<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Stream;

/**
 * Name DataStream.php
 *
 * Created 11/03/2020
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 *
 */

use App\Models\Elastic;
use App\Models\Settings;
use App\Models\Network;
use App\Libraries\CafeVariome\Net\QueryNetworkInterface;
use App\Models\Upload;
use App\Models\Source;
use App\Models\EAV;
use App\Libraries\CafeVariome\Core\DataPipeLine\Stream\Neo4J;
use App\Models\Pipeline;
use App\Libraries\CafeVariome\Core\IO\FileSystem\SysFileMan;
use App\Libraries\CafeVariome\Net\ServiceInterface;

class DataStream
{
    private $setting;
    private $elasticClient;
    protected $source_id;

    public function __construct(int $source_id) {

        $this->source_id = $source_id;

        $this->setting =  Settings::getInstance();
        $hosts = (array)$this->setting->getElasticSearchUri();
        $this->serviceInterface = new ServiceInterface();

        $this->serviceInterface->RegisterProcess($source_id, 1, 'elasticsearchindex', "Starting");

        $this->elasticClient = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
    }

    public function generateAttributeValueIndex(int $source_id)
    {
        $jsonIndexPath = getcwd() . DIRECTORY_SEPARATOR.  JSON_DATA_DIR;

        $sourceModel = new Source();
        $networkModel = new Network();
        $fileMan = new SysFileMan($jsonIndexPath);

        $sourceModel->lockSource($source_id);

        //Get network(s) source is assigned to
        $networks = $networkModel->getNetworksBySource($source_id);

        //Get all sources that belong to the above network(s)
        $networkAssignedSources = $sourceModel->getSourcesByNetworks($networks);

        $files = $fileMan->getFiles();

        foreach ($files as $file) {
            if (strpos($file, '.')) {
                $fArr = explode('.', $file);
                $fExt = $fArr[count($fArr) - 1];
                if (strtolower($fExt) == 'html' || strtolower($fExt) == 'htaccess') continue;

                foreach ($networks as $network_key) {
                    $redundantFile = ($file == $network_key . '.json' ||
                                     $file == $network_key . '_hpo_ancestry.json' ||
                                     $file == 'local_' . $network_key . '.json' ||
                                     $file == 'local_' . $network_key . '_hpo_ancestry.json');

                    if ($redundantFile && $fileMan->Exists($file)){
                        $fileMan->Delete($file);
                    }
                }
            }
        }

        foreach ($networkAssignedSources as $networkSourcePair) {
            try {
                $sourceData = $this->loadSourceEAVData($networkSourcePair['source_id'], $networkSourcePair['network_key']);

                $json_data = [];
                $existing_data = [];
                foreach ($sourceData as $d) {
                    $json_data[] = array("attribute" => $d['phenotype_attribute'], "value" => rtrim($d['phenotype_values'], "|"));
                }

                if ($fileMan->Exists($networkSourcePair['network_key'] . ".json")) {
                    $existing_data_json = $fileMan->Read($networkSourcePair['network_key'] . ".json");
                    $existing_data = json_decode($existing_data_json, true);
                }

                $json_data = array_merge($existing_data, $json_data);

                //Check the length of the data array, if it is empty, don't append it to the file.
                if (count($json_data) > 0) {
                    //Data must be written to the file in every iteration, in case the file is overwritten, some data is lost.
                    $fileMan->Write($networkSourcePair['network_key'] . ".json", json_encode($json_data));
                }
            } catch (\Exception $ex) {
                var_dump($ex);
            }
        }
    }

    public function generateHPOIndex(int $source_id)
    {
        $eavModel = new EAV();
        $networkModel = new Network();
        $neo4jInterface = new Neo4j();

        $results = $networkModel->getNetworkSourcesForCurrentInstallation($source_id);
        $sourceslist = []; // NEED to DO THIS per NETWORK!!!!
        $jsonIndexPath = getcwd() . DIRECTORY_SEPARATOR.  JSON_DATA_DIR;

        foreach ($results as $result) {
            $network = $result['network_key'];
            $sid = $result['source_id'];
            if (!isset($sourceslist[$network])) $sourceslist[$network] = [];
            array_push($sourceslist[$network], $sid);
        }

        foreach ($sourceslist as $network => $sourcelist) {

            $hpo_terms = $eavModel->getHPOTermsForSources($sourcelist);

            $hpo = [];
            $hpoTermsProcessed = 0;

            if (count($hpo_terms) > 0) {
                $this->serviceInterface->ReportProgress($this->source_id, $hpoTermsProcessed, count($hpo_terms), 'elasticsearchindex', 'Processing HPO terms');
            }

            foreach ($hpo_terms as $term){
				$term = strtoupper($term);
				$matchedTerms = $neo4jInterface->MatchHPO_IS_A($term);
                $pars = [];
                $termname = '';
                foreach ($matchedTerms as $record) {
                    array_push($pars, $record->get('ph'));
                    $termname = $record->get('termname');
                }
                $term .= ' (' . $termname . ')';
                $ancestors = $this->collect_ids_neo4j('', $pars);
                $hpo[$term] = $ancestors;

                $flag = false;
                while(!$flag) {
                    $flag = true;
                    $parents = [];
                    foreach ($hpo[$term] as $key => $ancestor) {
                        $temp = explode('|', $ancestor);
                        $t = end($temp);

                        if($t !== 'HP:0000001') {
                            $matchedTerms = $neo4jInterface->MatchHPO_IS_A($t);
                            $pars = [];
                            $termname = '';
                            foreach ($matchedTerms as $record) {
                                array_push($pars, $record->get('ph'));
                            }
                            $parents = array_merge($parents, $this->collect_ids_neo4j($ancestor, $pars));
                            $flag = false;
                        } else {
                            $parents[] = $ancestor;
                        }
                    }
                    $hpo[$term] = $parents;
                }

                $hpoTermsProcessed++;

                $this->serviceInterface->ReportProgress($this->source_id, $hpoTermsProcessed, count($hpo_terms), 'elasticsearchindex');
            }

            foreach($hpo as $term => $ancestory) {
                $hpo[$term] = implode('||', $ancestory);
            }

            file_put_contents($jsonIndexPath . $network . "_hpo_ancestry.json", json_encode($hpo));
        }
    }

    function collect_ids_neo4j($ancestor, $data) {
        $arr = [];
        foreach ($data as $d) {
            $arr[] = $ancestor === '' ? $d : $ancestor . '|'. $d;
        }
        return $arr;
    }

    /**
     * generateElasticSearchIndex
     *
     *
     */
    public function generateElasticSearchIndex(int $source_id, bool $add)
    {
        $elasticModel = new Elastic();

        $title = $elasticModel->getTitlePrefix();
        $index_name = $title."_".$source_id;

        $hosts = (array)$this->setting->getElasticSearchUri();
        $elasticClient = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
        $sourceModel = new Source();
        $uploadModel = new Upload();
        $eavModel = new EAV();
        $source_name = $sourceModel->getSourceNameByID($source_id);

        // Get the source id of the source we are working with
        $params = [];

        // ElasticSearch index name
        $params['index'] = $index_name;

        if (!$add) {
            $eavModel->resetElasticFlag($source_id);     // Reset elastic flag in eavs so that records appear in later selects
        }

        // Check whether an Index already exists
        $flag = false;
        if ($elasticClient->indices()->exists($params)){
            // If we are not adding to the index then we need to delete the current index
            if (!$add) {
                $response = $elasticClient->indices()->delete($params);
                $flag = true;
            }
        }
        else{
            $flag = true;
        }

        // If we need to - create a new index
        if ($flag) {
            $map = '{
                "mappings":{
                    "properties":{
                        "eav_rel":{"type": "join", "relations": {"sub":"eav"}},
                        "type": { "type": "keyword" },
                        "subject_id": {"type": "keyword"},
                        "source": {"type":"keyword"},
                        "attribute":{"type":"keyword"},
                        "value":{
                            "type":"text",
                            "fields":
                                {"raw":{"type": "keyword"},
                                "d":{"type": "double", "ignore_malformed": "true"},
                                "dt":{"type": "date", "ignore_malformed": "true", "format": "dateOptionalTime"}}}
                    }
                }
            }';

            $map2 = json_decode($map,1);
            $params['body'] = $map2;
            $response = $elasticClient->indices()->create($params);
        }
        // Set the elastic state of data to stale
        $sourceModel->updateSource(["elastic_status"=>0], ["source_id" => $source_id]);

		// Figure out how many documents we need to index
		$eavsize = $eavModel->countUnaddedEAVs($source_id);
		// Get all the unique subject ids for this source
		$uniqueIdsCount = $eavModel->countUniqueUIDs($source_id);

		$totalRecords = $eavsize + $uniqueIdsCount; //Total number of Elasticsearch documents to be created.

		$bulk = [];
		$countparents = 0;

		$this->serviceInterface->ReportProgress($source_id, $countparents, $totalRecords, 'elasticsearchindex', 'Generating Elasticsearch index');

		$offset = 0;
		$currId = 0;
		$batchSize = EAV_BATCH_SIZE;
		while ($offset < $uniqueIdsCount){

			$unique_ids = $eavModel->getEAVs('uid,subject_id', ["source_id"=>$source_id, "elastic"=>0, 'id>' => $currId], true, $batchSize);
			$uid_count = count($unique_ids);
			if ($uid_count == 0){
				break;
			}
			$currId = $eavModel->getLastIdByUID($unique_ids[$uid_count - 1]['uid']);

			// start making all the parent documents in ElasticSearch
			for ($i=0; $i < $uid_count; $i++) {
				$bulk['body'][] = ["index"=>[ "_id"=>$unique_ids[$i]['uid'],"_index"=>$index_name]];
				$bulk['body'][] = ["subject_id"=>$unique_ids[$i]['subject_id'], "eav_rel"=>["name"=>"sub"], "type"=>"sub", "source"=>$source_name."_eav"];

				unset($unique_ids[$i]);

				$countparents++;

				if ($countparents % EAV_BATCH_SIZE == 0){
					$responses = $elasticClient->bulk($bulk);
					$bulk=[];
					unset ($responses);

					$this->serviceInterface->ReportProgress($source_id, $countparents, $totalRecords, 'elasticsearchindex');
				}
			}

			$offset += $batchSize;
		}


		// Send the last parents through who didnt get finished in loop
		if (!empty($bulk['body'])){
			$responses = $elasticClient->bulk($bulk);
			$bulk=[];
			unset ($responses);

			$this->serviceInterface->ReportProgress($source_id, $countparents, $totalRecords, 'elasticsearchindex');
		}

		$bulk=[];
		$offset = 0;
		$counta = 0;
		$currId = 0;
		$batchSize = EAV_BATCH_SIZE;

		while ($offset < $eavsize){
			// Get current limit chunk of data
			$eavdata = $eavModel->getEAVs('id,uid,subject_id,attribute,value', ["source_id"=>$source_id, "elastic"=>0, 'id>' => $currId], false, $batchSize);

			$eav_count = count($eavdata);

			$lastRec = end($eavdata);
			$currId = $lastRec['id'];
			reset($eavdata);

			// Loop through limit chunk
			for ($i=0; $i < $eav_count; $i++) {
				$eavdata[$i]['attribute'] = preg_replace('/\s+/', '_', $eavdata[$i]['attribute']);
				$bulk['routing'] = $eavdata[$i]['uid'];
				$bulk['body'][] = ["index"=>["_index"=>$index_name]];
				$bulk['body'][] = ["subject_id"=>$eavdata[$i]['subject_id'],"attribute"=>$eavdata[$i]['attribute'],"value"=>strtolower($eavdata[$i]['value']), "eav_rel"=>["name"=>"eav","parent"=>$eavdata[$i]['uid']], "type"=>"eav", "source"=>$source_name."_eav"];

				unset($eavdata[$i]);

				$counta++;

				if ($counta % EAV_BATCH_SIZE == 0){
					$responses = $elasticClient->bulk($bulk);
					$bulk=[];
					unset ($responses);

					$this->serviceInterface->ReportProgress($source_id, $countparents + $counta, $totalRecords, 'elasticsearchindex');
				}
			}

			// Update offset
			$offset += $batchSize;
		}

        // Send the last set of documents through
        if (!empty($bulk['body'])){
            $responses = $elasticClient->bulk($bulk);
            $this->serviceInterface->ReportProgress($source_id, $countparents + $counta, $totalRecords, 'elasticsearchindex');
        }

        $eavModel->setElasticFlag($source_id);
        $sourceModel->unlockSource($source_id);

    }

    public function Neo4JInsert(int $source_id)
    {
        $neo4jInterface = new Neo4j();
        $eavModel = new EAV();
        $sourceModel = new Source();
        $pipelineModel = new Pipeline();
        $uploadModel = new Upload();

        $pipeline_ids = $uploadModel->getPipelineIdsBySourceId($source_id);
        $pipelines = $pipelineModel->getPipelinesByIds($pipeline_ids);

        $hpo_attribute_names = $this->getHPOAttributeNames($pipelines);
        $negated_hpo_attribute_names = $this->getNegatedHPOAttributeNames($pipelines);
        $orpha_attribute_names = $this->getORPHAAttributeNames($pipelines);
		$source_name = $sourceModel->getSourceNameByID($source_id);

        $batch = md5(uniqid(rand(),true));

        $unaddedHPOTerms = $eavModel->countUnaddedRecordsByAttributeNames($source_id, $hpo_attribute_names);
		$currId = 0;

		$i = 0;
        while ($i < $unaddedHPOTerms)
		{
			$HPOData = $eavModel->getHPOTermsBySourceId($source_id, $hpo_attribute_names, NEO4J_BATCH_SIZE, $currId);
			$subject_ids = $this->extractSubjectIDs($HPOData);

			if ($source_name != null) {
				$neo4jInterface->InsertSubjects($subject_ids, $source_name, $batch);
				$neo4jInterface->ConnectSubjects($HPOData, 'HPOterm', 'hpoid', 'hpo', $this->source_id);
			}

			$currId = end($HPOData)['id'];
			$i += count($HPOData);
		}

		$unaddedNegatedHPOTerms = $eavModel->countUnaddedRecordsByAttributeNames($source_id, $negated_hpo_attribute_names);
		$currId = 0;

		$i = 0;
		while ($i < $unaddedNegatedHPOTerms)
		{
			$NegatedHPOData = $eavModel->getNegatedHPOTermsBySourceId($source_id, $negated_hpo_attribute_names, NEO4J_BATCH_SIZE, $currId);
			$subject_ids = $this->extractSubjectIDs($NegatedHPOData);

			if ($source_name != null) {
				$neo4jInterface->InsertSubjects($subject_ids, $source_name, $batch);
				$neo4jInterface->ConnectSubjects($NegatedHPOData, 'HPOterm', 'hpoid', 'negated_hpo', $this->source_id);
			}

			$currId = end($NegatedHPOData)['id'];
			$i += count($NegatedHPOData);
		}

		$currId = 0;
		$unaddedOrphaTerms = $eavModel->countUnaddedRecordsByAttributeNames($source_id, $orpha_attribute_names);

		$i = 0;
		while ($i < $unaddedOrphaTerms)
		{
			$ORPHAData = $eavModel->getORPHATermsBySourceId($source_id, $orpha_attribute_names, NEO4J_BATCH_SIZE, $currId);
			$subject_ids = $this->extractSubjectIDs($ORPHAData);

			if ($source_name != null) {
				$neo4jInterface->InsertSubjects($subject_ids, $source_name, $batch);
				$neo4jInterface->ConnectSubjects($ORPHAData, 'ORPHAterm', 'orphaid', 'orpha', $this->source_id);
			}

			$currId = end($ORPHAData)['id'];
			$i += count($ORPHAData);
		}

	}

    public function Finalize(int $source_id)
    {
        $this->serviceInterface->ReportProgress($source_id, 1, 1, 'elasticsearchindex', 'Finished', true);
    }

    private function getHPOAttributeNames(array $pipelines) : array
    {
        $names = [];
        foreach ($pipelines as $pipeline) {
            $names[] = $pipeline['hpo_attribute_name'];
        }

        return $names;
    }

    private function getNegatedHPOAttributeNames(array $pipelines) : array
    {
        $names = [];
        foreach ($pipelines as $pipeline) {
            $names[] = $pipeline['negated_hpo_attribute_name'];
        }

        return $names;
    }

    public function getORPHAAttributeNames(array $pipelines) : array
    {
        $names = [];
        foreach ($pipelines as $pipeline) {
            $names[] = $pipeline['orpha_attribute_name'];
        }

        return $names;
    }


    /**
     * loadSourceEAVData
     *
     *
     * @param int $source_id
     * @param string $network_key
     *
     * @return array localPhenoTypes
     */
    private function loadSourceEAVData(int $source_id, string $network_key) {

        $eavModel = new EAV();
        $sourceModel = new Source();

        $eavCount = $eavModel->getEAVs('count(*) as totalEAVs', ['source_id' => $source_id]);
        $totalEAVRecords = $eavCount[0]['totalEAVs'];

        $source_name = $sourceModel->getSourceNameByID($source_id);

        $data = [];
        $tempLocalPhenotypes = [];
        $recordsProcessed = 0;

        if ($totalEAVRecords > 0) {
            $this->serviceInterface->ReportProgress($source_id, $recordsProcessed, $totalEAVRecords, 'elasticsearchindex', 'Processing attributes and values for: ' . $source_name);
        }

        $batchSize = EAV_BATCH_SIZE;
	    $currId = 0;

        for ($i=0; $i < $totalEAVRecords; $i+=$batchSize) {
            $data = $eavModel->getEAVsForSource($source_id, $batchSize, $currId);
            $this->swapLocalPhenotypes($data, $tempLocalPhenotypes, $network_key);
            $recordsProcessed += count($data);
            $this->serviceInterface->ReportProgress($source_id, $recordsProcessed, $totalEAVRecords, 'elasticsearchindex');
            $currId = end($data)['id'];
        }

        return $tempLocalPhenotypes;
    }

    private function swapLocalPhenotypes(array $data, & $tempLocalPhenotypes, int $network_key)
    {
        foreach ($data as $d) {

            $attr = $d['attribute'];
            $value = $d['value'];

            if(strlen($value) > 229) continue;

            if(is_numeric($value)) {
                $sigs = 4;
                if(is_float($value) && floatval($value)) {
                    if($value < 0) {
                        $value = round($value * -1, $sigs) * -1;
                    } else {
                        $value = round($value, $sigs);
                    }
                }
            }

            $value = (string)$value;

            $local_phenotypes = [];

            foreach ($tempLocalPhenotypes as $tlp) {
                if ($tlp['phenotype_attribute'] == $attr) {
                    array_push($local_phenotypes, $tlp);
                }
            }

            if(count($local_phenotypes) > 0) {
                $lastLP = array_pop($local_phenotypes);
                if(in_array($value, explode("|" , $lastLP['phenotype_values'])) || (strpos($lastLP['phenotype_values'], 'Not all values displayed|') !== false)) continue;
                else {
                    // Allow displaying of all values
                    $val = $lastLP['phenotype_values'] . $value . "|";
                    $tempLocalPhenotypes[$attr]['phenotype_values'] = $val;
                    $tempLocalPhenotypes[$attr]['phenotype_attribute'] = $attr;
                }
            } else {
                $value = $value . "|";

                $tempLocalPhenotypes[$attr] = ["network_key" => $network_key, "phenotype_attribute" => $attr, "phenotype_values" => $value];
            }
        }
    }

	private function extractSubjectIDs(array $data): array
	{
		$subject_ids = [];
		for ($i = 0; $i < count($data); $i++){
			if (!in_array($data[$i]['subject_id'], $subject_ids)){
				array_push($subject_ids, $data[$i]['subject_id']);
			}
		}

		return $subject_ids;
    }

    function createDocByAttribute(string $index_name, string $attribute, bool $range_exist) {
        $values = [];
        $uid = "";
        if ($range_exist) {
            $paramsnew = ['index' => $index_name, 'size' => 100];
            $paramsnew['body']['query']['bool']['must'][0]['has_parent']['parent_type'] = "att";
            $paramsnew['body']['query']['bool']['must'][0]['has_parent']['query']['bool']['must'][0]['match']['attribute.raw'] = $attribute;
            $paramsnew['body']['query']['bool']['must'][1]['match']['type_of_doc'] = "range";
            $hits = $this->elasticClient->search($paramsnew);
            foreach ($hits['hits']['hits'] as $hit) {
                if (empty($uid)) {
                    $uid = $hit['_routing'];
                }
                $temp = explode("-", $hit['_source']['value']);
                foreach ($temp as $t) {
                    array_push($values, $t);
                }
            }
        }
        $paramsnew = ['index' => $index_name, 'size' => 100];
        $paramsnew['body']['query']['bool']['must'][0]['has_parent']['parent_type'] = "att";
        $paramsnew['body']['query']['bool']['must'][0]['has_parent']['query']['bool']['must'][0]['match']['attribute.raw'] = $attribute;
        $paramsnew['body']['query']['bool']['must'][1]['match']['type_of_doc'] = "value";
        $hits = $this->elasticClient->search($paramsnew);
        foreach ($hits['hits']['hits'] as $hit) {
            if (empty($uid)) {
                $uid = $hit['_routing'];
            }
            array_push($values, $hit['_source']['value']);
        }
        if (count($values) > 0) {
            $value = min($values)."-".max($values);
            $bulk=[];
            $params = [
                'index' => $index_name,
                'type'  => "attributes",
                'routing' => $uid,
                'body' => ["value"=>$value,"type_of_doc"=>"overall", "eav_rel"=>["name"=>"val","parent"=>$uid]]
            ];

            $response = $this->elasticClient->index($params);
        }

    }

    function deleteDocByAttribute(string $index_name, string $attribute) {

        $params = [];
        $params['index'] = $index_name;

        $params['body']['query']['bool']['must'][0]['has_parent']['parent_type'] = "att";
        $params['body']['query']['bool']['must'][0]['has_parent']['query']['bool']['must'][0]['match']['attribute.raw'] = $attribute;
        $params['body']['query']['bool']['must'][1]['match']['type_of_doc'] = "overall";

        return $this->deleteDocByQuery($params);
    }

    function deleteDocByInstallationKey(string $index_name, string $installation_key) {

        $params = [];
        $params['index'] = $index_name;
        $params['body']['query']['term']['installation'] = $installation_key;

        return $this->deleteDocByQuery($params);
    }

    function deleteDocByQuery(array $params) {
        return $this->elasticClient->deleteByQuery($params);
    }
}
