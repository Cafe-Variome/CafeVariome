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
use App\Models\Neo4j;
use App\Models\Phenotype;
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
        $phenotypeModel = new Phenotype();
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
                $sourceData = $phenotypeModel->localPhenotypesLookupValues($networkSourcePair['source_id'], $networkSourcePair['network_key']);

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
        $neo4jModel = new Neo4j();

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
                $matchedTerms = $neo4jModel->MatchHPO($term);
                $pars = [];
                $termname = '';
                foreach ($matchedTerms->getRecords() as $record) {
                    array_push($pars, $record->value('ph'));
                    $termname = $record->value('termname');
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
                            $matchedTerms = $neo4jModel->MatchHPO($t);
                            $pars = [];
                            $termname = '';
                            foreach ($matchedTerms->getRecords() as $record) {
                                array_push($pars, $record->value('ph'));
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

        // Check whether an Index already exists
        $flag = false;
        if ($elasticClient->indices()->exists($params)){
            // If we are not adding to the index then we need to delete the current index
            if (!$add) {
                $response = $elasticClient->indices()->delete($params);
                $flag = true;
                $eavModel->updateEAVs(["elastic"=>0], ['source_id'=> $source_id]) ;     // Reset elastic flag in eavs so that records appear in later selects
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
        $eavsize = count($eavModel->getEAVs('uid,subject_id', ["source_id"=>$source_id, "elastic"=>0]));

        // Get all the unique subject ids for this source
        $unique_ids = $eavModel->getEAVs('uid,subject_id', ["source_id"=>$source_id, "elastic"=>0], true);

        $totalRecords = $eavsize + count($unique_ids); //Total number of Elasticsearch documents to be created.

        $bulk = [];
        $counta = 0;
        $countparents = 0;

        $this->serviceInterface->ReportProgress($source_id, $countparents, $totalRecords, 'elasticsearchindex', 'Generating Elasticsearch index');

        // start making all the parent documents in ElasticSearch
        foreach($unique_ids as $index_data){
            $bulk['body'][] = ["index"=>[ "_id"=>$index_data['uid'],"_index"=>$index_name]];
            $bulk['body'][] = ["subject_id"=>$index_data['subject_id'], "eav_rel"=>["name"=>"sub"], "type"=>"sub", "source"=>$source_name."_eav"];    

            $countparents++;

            if ($countparents%500 == 0){
                $responses = $elasticClient->bulk($bulk);
                $bulk=[];
                unset ($responses);

                $this->serviceInterface->ReportProgress($source_id, $countparents, $totalRecords, 'elasticsearchindex');
            }
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

        while ($offset < $eavsize){
            // Get current limit chunk of data
            $eavdata = $eavModel->getEAVs(null, ["source_id"=>$source_id, "elastic"=>0], false, 1000, $offset);

            // Loop through limit chunk
            foreach ($eavdata as $attribute_array){
                $attribute_array['attribute'] = preg_replace('/\s+/', '_', $attribute_array['attribute']);
                $bulk['routing'] = $attribute_array['uid'];
                $bulk['body'][] = ["index"=>["_index"=>$index_name]];
                $bulk['body'][] = ["subject_id"=>$attribute_array['subject_id'],"attribute"=>$attribute_array['attribute'],"value"=>strtolower($attribute_array['value']), "eav_rel"=>["name"=>"eav","parent"=>$attribute_array['uid']], "type"=>"eav", "source"=>$source_name."_eav"];
                $counta++;
                // Every 500 documents bulk insert to ElasticSearch
                if ($counta%500 == 0){
                    $responses = $elasticClient->bulk($bulk);
                    $bulk=[];
                    unset ($responses);

                    $this->serviceInterface->ReportProgress($source_id, $countparents + $counta, $totalRecords, 'elasticsearchindex');
                }   
            }
            // Update offset 
            $offset += 1000;
        }

        // Send the last set of documents through
        if (!empty($bulk['body'])){
            $responses = $elasticClient->bulk($bulk);
            $this->serviceInterface->ReportProgress($source_id, $countparents + $counta, $totalRecords, 'elasticsearchindex');
        }   

        $sourceModel->unlockSource($source_id);	
        $eavModel->setElasticFlag($source_id);     

    }

    public function Neo4JInsert(int $source_id)
    {
        $neo4jModel = new Neo4j();
        $eavModel = new EAV();
        $sourceModel = new Source();
        $pipelineModel = new Pipeline();
        $uploadModel = new Upload();

        $pipeline_ids = $uploadModel->getPipelineIdsBySourceId($source_id);
        $pipelines = $pipelineModel->getPipelinesByIds($pipeline_ids);
        //'hpo_attribute_name, negated_hpo_attribute_name, orpha_attribute_name'

        $hpo_attribute_names = $this->getHPOAttributeNames($pipelines);
        $negated_hpo_attribute_names = $this->getNegatedHPOAttributeNames($pipelines);
        $orpha_attribute_names = $this->getORPHAAttributeNames($pipelines);

        $batch = md5(uniqid(rand(),true));	
        $HPOData = $eavModel->getHPOTermsWithNegatedBySourceId($source_id, $hpo_attribute_names, $negated_hpo_attribute_names); 
        $ORPHAData = $eavModel->getORPHATerms($source_id, $orpha_attribute_names); 
        $source_name = $sourceModel->getSourceNameByID($source_id);

        if ($source_name != null) {
            $neo4jModel->InsertSubjects($HPOData, $source_name, $batch);
            $neo4jModel->InsertSubjects($ORPHAData, $source_name, $batch);
            
            $neo4jModel->ConnectSubjects($HPOData, 'HPOterm', 'hpoid', 'hpo', $this->source_id);
            $neo4jModel->ConnectSubjects($ORPHAData, 'ORPHAterm', 'orphaid', 'orpha', $this->source_id);
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
