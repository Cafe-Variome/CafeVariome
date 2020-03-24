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


class DataStream
{
    private $setting;
    private $elasticClient;

    public function __construct() {

        $this->setting =  Settings::getInstance();
        $hosts = (array)$this->setting->getElasticSearchUri();

        $this->elasticClient = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
        $elasticClient = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
    }


    /**
     * regenrateHDRSprintElasticIndex 
     * 
     * @author Gregory Warren
     * 
     */
    public function regenerateElasticSearchIndex(int $network_key, string $installation_key, $data) {

        $elasticModel = new Elastic();

        $title = $elasticModel->getTitlePrefix();
        
        $index_name = $title . "_autocomplete_" . $network_key;

        $params['index'] = $index_name;
        if (!$this->elasticClient->indices()->exists($params)){
            $map = '{
                    "settings": {
                      "index.max_ngram_diff" : 38,
                      "analysis": {
                         "filter": {
                            "nGram_filter": {
                               "type": "nGram",
                               "min_gram": 2,
                               "max_gram": 40,
                               "token_chars": [
                                  "letter",
                                  "digit",
                                  "punctuation",
                                  "symbol"
                               ]
                            }
                         },
                         "analyzer": {
                            "nGram_analyzer": {
                               "type": "custom",
                               "tokenizer": "whitespace",
                               "filter": [
                                  "lowercase",
                                  "asciifolding",
                                  "nGram_filter"
                               ]
                            },
                            "whitespace_analyzer": {
                               "type": "custom",
                               "tokenizer": "whitespace",
                               "filter": [
                                  "lowercase",
                                  "asciifolding"
                               ]
                            }
                         }
                      }
                   },
                   "mappings" : {
                        "properties" : {
                            "eav_rel":{"type": "join", "relations": {"att":"val"}},
                            "attribute" : {
                                "type" : "text",
                                "fields" : {
                                    "raw" : {
                                    "type" : "keyword"
                                    }
                                },
                                "analyzer" : "nGram_analyzer",
                                "search_analyzer" : "whitespace_analyzer"
                            },
                            "value": {
                                "type": "keyword"
                                },
                            "type_of_doc": {
                                "type": "keyword"
                            },
                            "installation": {
                                "type": "keyword"
                            }
                        }
                        
                    }
                }';
            $map2 = json_decode($map,1);
            $params['body'] = $map2;
            $response = $this->elasticClient->indices()->create($params);
        }
        $this->deleteDocByInstallationKey($index_name, $installation_key);

        $params = ['index' => $index_name, 'size' => 10000];
        $params['body']['query']['match']['type_of_doc'] = "attribute";
        $response = $this->elasticClient->search($params);
        $existing_atts = [];
        $numeric = [];
        for ($i=0; $i < count($response['hits']['hits']); $i++) { 
            $existing = $response['hits']['hits'][$i]['_source']['attribute'];
            $uid = $response['hits']['hits'][$i]['_id'];
            $existing_atts[$existing] = $uid;
            // array_push($existing_atts, $response['hits']['hits'][$i]['_source']['att']);
        }
        unset($response, $params, $map2, $map);
        $bulk=[];
        $excluded = ['ancestor_hpo_id','ancestor_hpo_label','phenotypes_label','phenotypes_id'];
        $att_count = 0;
        $count = 0;

        $data = str_replace("'", "''", $data);

        $jdata = json_decode($data, 1);
        $jle = json_last_error();
        $jlem = json_last_error_msg();
        foreach (json_decode($data, 1) as $res) {
            // error_log($res['attribute'])
            if (in_array($res['attribute'], $excluded)) {
                continue;
            }
            if (!array_key_exists($res['attribute'], $existing_atts)) {
                $uid = md5(uniqid(rand(),true));
                $bulk['body'][] = ["index"=>["_id" => $uid, "_index"=>$index_name]];
                $bulk['body'][] = ["attribute"=>$res['attribute'],"type_of_doc"=>"attribute","eav_rel"=>["name"=>"att"]];
                $count++;
                $att_count++;
                if ($count%500 == 0){
                    $responses = $this->elasticClient->bulk($bulk);
                    $bulk=[];
                    unset ($responses);                
                }
            }
            else {
                $uid = $existing_atts[$res['attribute']];
            }
            $target = 1;
            $range = 0;
            foreach (explode("|", strtolower($res['value'])) as $val) {
                if (preg_match("/\d*?\.\d*?-\d*?\.\d*/", $val)) {
                    $bulk['body'][] = ["index"=>["_id" => $uid, "_index"=>$index_name]];
                    $bulk['body'][] = ["value"=>$val,"installation"=> $installation_key,"type_of_doc"=>"range", "eav_rel"=>["name"=>"val","parent"=>$uid]];
                    $range = 1;
                }
                else {
                    $bulk['body'][] = ["index"=>["_id" => $uid, "_index"=>$index_name]];
                    $bulk['body'][] = ["value"=>$val,"installation"=> $installation_key,"type_of_doc"=>"value", "eav_rel"=>["name"=>"val","parent"=>$uid]];
                    $jb = json_encode(["index"=>["_id" => $uid, "_index"=>$index_name]]);
                    $jb2 = json_encode(["value"=>$val,"installation"=> $installation_key,"type_of_doc"=>"value", "eav_rel"=>["name"=>"val","parent"=>$uid]]);
                    
                    if (!is_numeric($val)) {
                        $target = 0;
                    }
                }               
                $count++;
                if ($count%500 == 0)
                {
                    $responses = $this->elasticClient->bulk($bulk);
                    $bulk=[];
                    unset ($responses);                
                }
            } 
            if ($target) {
                $numeric[$res['attribute']] = $range;
            }      
        }
        if (!empty($bulk['body'])){
            error_log("final");
            $responses = $this->elasticClient->bulk($bulk);
        } 
        if (empty($numeric)) {
            //return;
        }
        $target = $count - $att_count;
        $bool = 1;
        //while ($bool) {
            $paramsnew = ['index' => $index_name, 'size' => 0];
            $paramsnew['body']['query']['bool']['must']['has_parent']['parent_type'] = "att"; 
            $paramsnew['body']['query']['bool']['must']['has_parent']['query']['match']['installation'] = $installation_key;

            $hits = $this->elasticClient->search($paramsnew);
            // error_log($hits['hits']['total']);
            if ($hits['hits']['total'] == $target) {
                $bool = 0;
            }
        //}
        error_log(print_r($numeric,1));
        $this->process_numeric_autocomplete($index_name, $numeric);
    }

    function process_numeric_autocomplete(string $index_name, $numeric) {
        foreach ($numeric as $att  => $range) {
            $this->deleteDocByAttribute($index_name, $att);
            if ($range) {
                $this->createDocByAttribute($index_name,$att,1);
                continue;
            } 
            $overall = 0;
            $paramsnew = ['index' => $index_name, 'size' => 10];
            $paramsnew['body']['query']['bool']['must'][0]['has_parent']['parent_type'] = "att"; 
            $paramsnew['body']['query']['bool']['must'][0]['has_parent']['query']['bool']['must'][0]['match']['attribute.raw'] = $att;
            $paramsnew['body']['query']['bool']['must'][1]['match']['type_of_doc'] = "range";
            $hits = $this->elasticClient->search($paramsnew);
            // error_log(print_r($hits,1));
            if ($hits['hits']['total']) {
                error_log("found");
                $this->createDocByAttribute($index_name,$att,1);
                continue;
            }
            $paramsnew = ['index' => $index_name, 'size' => 0];
            $paramsnew['body']['query']['bool']['must']['has_parent']['parent_type'] = "att"; 
            $paramsnew['body']['query']['bool']['must']['has_parent']['query']['bool']['must'][0]['match']['attribute.raw'] = $att;
            $paramsnew['body']['query']['bool']['must']['has_parent']['query']['bool']['must'][1]['match']['type_of_doc'] = "value";
            $paramsnew['body']['aggs']['value-count']['cardinality']['field'] = 'value';
            $hits = $this->elasticClient->search($paramsnew);
            if ($hits['aggregations']['value-count']['value'] > 20) {
                $this->createDocByAttribute($index_name,$att,0);
            }
        }
    }

    function pre_hpo_complete(array $installations, int $network_key) {

        $networkModel = new Network();

        $hpo_data = [];
        foreach ($installations as $installation) {
            $i_url = $installation->base_url;
            $i_key = $installation->installation_key;

            $queryNetInterface = new QueryNetworkInterface($installation->base_url);
            $chksumResp = $queryNetInterface->getJSONDataModificationTime((int) $network_key, Null, true, true);

            $data = [];
            if ($chksumResp->status) {
                $data = $chksumResp->data;
            }

            $networkModel->updateChecksum($data->checksum ,$network_key, $i_key, 1);
            if(property_exists($data, 'file')){
                $hpo_data = array_merge($hpo_data,json_decode($data->file, 1));
            }
        }
        $hpo_data = array_unique($hpo_data);
        file_put_contents("resources/phenotype_lookup_data/" . "local_" . $network_key . "_hpo_ancestry.json", json_encode($hpo_data));
        // error_log(print_r($))
        $keys = array_keys($hpo_data);
        $data = [];
        for ($i=0; $i < count($keys); $i++) { 
            preg_match("/(.*?)\s+\((.*)\)/", $keys[$i],$matches);
            $hpo_id = $matches[1];
            $hpo_label = $matches[2];
            if (empty($hpo_label)) {
                $hpo_label = $this->get_hpo_label($hpo_id);
            }
            $data[$i]['hpo_id'] = $hpo_id;
            $data[$i]['term'] = $hpo_label;
            $data[$i]['id_term'] = $keys[$i];
        }
        $this->update_hpo_complete($network_key,$data);
    }

    function update_hpo_complete($network_key,$hpo_data) {

        $elasticModel = new Elastic($this->db);
        $title = $elasticModel->getTitlePrefix();
        $index_name = $title . "_" . $network_key . "_hpo";

        $params['index'] = $index_name;
        if ($this->elasticClient->indices()->exists($params)){
            $response = $this->elasticClient->indices()->delete($params);
        }
        $map = '{
            "settings": {
                "index.max_ngram_diff" : 38,
                "analysis": {
                 "filter": {
                    "nGram_filter": {
                       "type": "nGram",
                       "min_gram": 2,
                       "max_gram": 40,
                       "token_chars": [
                          "letter",
                          "digit",
                          "punctuation",
                          "symbol"
                       ]
                    }
                 },
                 "analyzer": {
                    "nGram_analyzer": {
                       "type": "custom",
                       "tokenizer": "whitespace",
                       "filter": [
                          "lowercase",
                          "asciifolding",
                          "nGram_filter"
                       ]
                    },
                    "whitespace_analyzer": {
                       "type": "custom",
                       "tokenizer": "whitespace",
                       "filter": [
                          "lowercase",
                          "asciifolding"
                       ]
                    }
                 }
              }
           },
           "mappings" : {
                "properties": {
                    "hpoid": {
                        "type": "text",
                        "search_analyzer": "whitespace_analyzer",
                        "analyzer": "nGram_analyzer",
                        "fields": {
                            "raw": {
                                "type": "keyword"
                            }
                        }              
                    },
                    "term": {
                        "type": "text",
                        "search_analyzer": "whitespace_analyzer",
                        "analyzer": "nGram_analyzer",
                        "fields": {
                            "raw": {
                                "type": "keyword"
                            }
                        }
                    },
                    "id_term": {
                        "type":"keyword"
                    }
                }
                
            }
        }';
        $map2 = json_decode($map,1);
        $params['body'] = $map2;
        $response = $this->elasticClient->indices()->create($params);
        $bulk=[];
        $count = 0;
        foreach ($hpo_data as $hpo) {
            $bulk['body'][] = ["index"=>["_index"=>$index_name, "_type"=>"hpos"]];
            $bulk['body'][] = ["hpo_id"=>$hpo['hpo_id'],"term"=>$hpo['term'],"id_term" =>$hpo['id_term']];
            $count++;
            if ($count%500 == 0){
                $responses = $this->elasticClient->bulk($bulk);
                $bulk=[];
                unset ($responses);
            }     
        }
        if (!empty($bulk['body'])){
            $responses = $this->elasticClient->bulk($bulk);
            $bulk=[];
            unset ($responses);
        }   
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
