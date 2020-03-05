<?php namespace App\Libraries\CafeVariome;


use App\Helpers\AuthHelper;
use App\Models\Settings;
use App\Models\Source;
use App\Models\Network;
use App\Models\Elastic;
use App\Models\EAV;
use App\Libraries\ElasticSearch;
use Elasticsearch\ClientBuilder;

class Query extends CafeVariome{

    private $elasticClient;
    private $db;

    function __construct($parameters = []) {
        if (array_key_exists('syntax', $parameters)) {
            $this->syntax = $parameters['syntax'];
        } else {
            $this->syntax = 'elasticsearch';
        }
        $this->db = \Config\Database::connect();

        $this->setting =  Settings::getInstance($this->db);

        $hosts = array($this->setting->settingData['elastic_url']);
        $this->elasticClient =  ClientBuilder::create()->setHosts($hosts)->build();
    }

    /**
     * @deprecated
     */
    
    function parse($query) {
        
        $query_data = array();
        $query_data = $query['query'];

        foreach ($query_data as $k => $v) {
            foreach ($v as $element) {
                if (!$this->syntax == "elasticsearch")
                    continue;

                if($k == "coordinate") {
                    $element['operator'] = strtolower($element['operator']);
                    $chr = substr(explode(".",$element['reference']['id'])[0], 3);
                    $build = explode(".",$element['reference']['id'])[1];
                    $start = $element['start'];
                    $stop = $element['stop'];
                    $type = $element['reference_type'];
                    
                    if($element['operator'] == "exact") {
                        if($type == "genome") {
                            $query_array[$element['querySegmentID']] = "(genome_chr:" . $chr . " OR genome_chr:chr" . $chr . ") AND genome_build:" . $build . " AND genome_start_d:" . $start . " AND genome_stop_d:" . $stop;
                        } else {
                            $query_array[$element['querySegmentID']] = "accession_ref:" . $element['reference']['id'] . " AND accession_start_d:" . $start . " AND accession_stop_d:" . $stop;
                        }
                    } else if($element['operator'] == "exceed") {
                        if($type == "genome") {
                            $query_array[$element['querySegmentID']] = "(genome_chr:" . $chr . " OR genome_chr:chr" . $chr . ") AND genome_build:" . $build . " AND genome_start_d:<" . $start . " AND genome_stop_d:>" . $stop;
                        } else {
                            $query_array[$element['querySegmentID']] = "accession_ref:" . $element['reference']['id'] . " AND accession_start_d:<" . $start . " AND accession_stop_d:>" . $stop;
                        }
                    } else if($element['operator'] == "begin_between") {
                        if($type == "genome") {
                            $query_array[$element['querySegmentID']] = "(genome_chr:" . $chr . " OR genome_chr:chr" . $chr . ") AND genome_build:" . $build . " AND genome_start_d:>=" . $start . " AND genome_start_d:<=" . $stop;
                        } else {
                            $query_array[$element['querySegmentID']] = "accession_ref:" . $element['reference']['id'] . " AND accession_start_d:>=" . $start . " AND accession_start_d:<=" . $stop;
                        }
                    } else if($element['operator'] == "end_between") {
                        if($type == "genome") {
                            $query_array[$element['querySegmentID']] = "(genome_chr:" . $chr . " OR genome_chr:chr" . $chr . ") AND genome_build:" . $build . " AND genome_stop_d:>=" . $start . " AND genome_stop_d:<=" . $stop;
                        } else {
                            $query_array[$element['querySegmentID']] = "accession_ref:" . $element['reference']['id'] . " AND accession_stop_d:>=" . $start . " AND accession_stop_d:<=" . $stop;
                        }
                    } else if($element['operator'] == "begin_and_end_between") {
                        if($type == "genome") {
                            $query_array[$element['querySegmentID']] = "(genome_chr:" . $chr . " OR genome_chr:chr" . $chr . ") AND genome_build:" . $build . " AND genome_start_d:>=" . $start . " AND genome_stop_d:<=" . $stop;
                        } else {
                            $query_array[$element['querySegmentID']] = "accession_ref:" . $element['reference']['id'] . " AND accession_start_d:>=" . $start . " AND accession_stop_d:<=" . $stop;
                        }
                    } else if($element['operator'] == "only_begin_between") { 
                        if($type == "genome") {
                            $query_array[$element['querySegmentID']] = "(genome_chr:" . $chr . " OR genome_chr:chr" . $chr . ") AND genome_build:" . $build . " AND genome_start_d:>=" . $start . " AND genome_start_d:<=" . $stop . " AND genome_stop_d:>" . $stop;
                        } else {
                            $query_array[$element['querySegmentID']] = "accession_ref:" . $element['reference']['id'] . " AND accession_start_d:>=" . $start . " AND accession_start_d:<=" . $stop . " AND accession_stop_d:>" . $stop;
                        }
                    } else if($element['operator'] == "only_end_between") {
                        if($type == "genome") {
                            $query_array[$element['querySegmentID']] = "(genome_chr:" . $chr . " OR genome_chr:chr" . $chr . ") AND genome_build:" . $build . " AND genome_start_d:<" . $start . " AND genome_stop_d:>=" . $start . " AND genome_stop_d:<=" . $stop;
                        } else {
                            $query_array[$element['querySegmentID']] = "accession_ref:" . $element['reference']['id'] . " AND accession_start_d:<" . $start . " AND accession_stop_d:>=" . $start . " AND accession_stop_d:<=" . $stop;
                        }
                    } else if($element['operator'] == "begin_at_start") {
                        if($type == "genome") {
                            $query_array[$element['querySegmentID']] = "(genome_chr:" . $chr . " OR genome_chr:chr" . $chr . ") AND genome_build:" . $build . " AND genome_start_d:" . $start;
                        } else {
                            $query_array[$element['querySegmentID']] = "accession_ref:" . $element['reference']['id'] . " AND accession_start_d:" . $start;
                        }
                    } else if($element['operator'] == "end_at_stop") {
                        if($type == "genome") {
                            $query_array[$element['querySegmentID']] = "(genome_chr:" . $chr . " OR genome_chr:chr" . $chr . ") AND genome_build:" . $build . " AND genome_stop_d:" . $stop;
                        } else {
                            $query_array[$element['querySegmentID']] = "accession_ref:" . $element['reference']['id'] . " AND accession_stop_d:" . $stop;
                        }
                    }
                } else if ($k == "otherFields") {
                    $element['operator'] = strtolower($element['operator']);
                    $element['otherField'] = strtolower($element['otherField']);
                    $element['otherValue'] = strtolower($element['otherValue']);

                    if ($element['operator'] == "is")
                        $query_array[$element['querySegmentID']] = $element['otherField'] . ":" . $element['otherValue'];
                    else if ($element['operator'] == "is like")
                        $query_array[$element['querySegmentID']] = $element['otherField'] . ":*" . $element['otherValue'] . "*";
                    else if ($element['operator'] == "is not")
                        $query_array[$element['querySegmentID']] = $element['otherField'] . ":(-" . $element['otherValue'] . ")";
                    else if ($element['operator'] == "is not like")
                        $query_array[$element['querySegmentID']] = $element['otherField'] . ":(-*" . $element['otherValue'] . "*)";
                    else {
                        $element['otherValue'] = str_replace('-', '\-', $element['otherValue']); // Escape
                        $element['otherValue'] = str_replace('+', '\+', $element['otherValue']); // Escape
                        if ($element['operator'] == "=" && is_numeric($element['otherValue'])) {
                            $query_array[$element['querySegmentID']] = $element['otherField'] . "_d:" . $element['otherValue'];
                        } else if ($element['operator'] == "!=" && is_numeric($element['otherValue'])) {
                            $query_array[$element['querySegmentID']] = $element['otherField'] . "_d:(" . "<" . $element['otherValue'] . " OR >" . $element['otherValue'] . ")";
                        } else {
                            $query_array[$element['querySegmentID']] = $element['otherField'] . "_d:" . "" . $element['operator'] . "" . $element['otherValue'];
                        }
                    }
                } else if ($k == "phenotypeFeature") {
                    $attribute = $element['phenotypeConcept']['cursivePhenotypeConcept']['term'];
                    $value = strtolower($element['phenotypeFeature']['value']);

                    $attribute = str_replace(' ', '_', $attribute); // Replace spaces with underscore as this is how the phenotype attribute is indexed in ElasticSearch (ElasticSearch can't handle spaces in a field name so have removed spaces and replaced with underscore)
                    $attribute = str_replace('[', '\[', $attribute); // Escape square brackets as these are reserved in ElasticSearch
                    $attribute = str_replace(']', '\]', $attribute); // Escape square brackets as these are reserved in ElasticSearch

                    if (strtolower($element['operator']) == "is") {
                        if (strtolower($value) == "null") {
                            $query_array[$element['querySegmentID']] = "_missing_:" . $attribute;
                        } else {
                            $value = addcslashes($value, '-+=&&||><!\(\)\{\}\[\]^"~*?:\\');
                            $query_array[$element['querySegmentID']] = $attribute . "_raw:" . $value;
                        }
                    } else if (strtolower($element['operator']) == "is like") {
                        $value = addcslashes($value, '-+=&&||><!\(\)\{\}\[\]^"~*?:\\');
                        $query_array[$element['querySegmentID']] = $attribute . "_raw:" . "*" . $value . "*";
                    } else if (strtolower($element['operator']) == "is not") {
                        if (strtolower($value) == "null") {
                            $query_array[$element['querySegmentID']] = "_exists_:" . $attribute;
                        } else {
                            $value = addcslashes($value, '-+=&&||><!\(\)\{\}\[\]^"~*?:\\');
                            $query_array[$element['querySegmentID']] = $attribute . "_raw:" . "(-" . $value . ")";
                        }
                    } else if (strtolower($element['operator']) == "is not like") {
                        if (strtolower($value) == "null") {
                            $query_array[$element['querySegmentID']] = "_exists_:" . $attribute;
                        } else {
                            $value = addcslashes($value, '-+=&&||><!\(\)\{\}\[\]^"~*?:\\');
                            $query_array[$element['querySegmentID']] = $attribute . "_raw:" . "(-*" . $value . "*)";
                        }
                    } else if (strtolower($element['operator']) == "=") {
                        if (strtolower($value) == "null") {
                            $query_array[$element['querySegmentID']] = "_missing_:" . $attribute;
                        } else {
                            if (is_numeric($value)) {
                                $value = str_replace('-', '\-', $value); // Escape
                                $value = str_replace('+', '\+', $value); // Escape
                                $query_array[$element['querySegmentID']] = $attribute . "_d:" . $value;
                            }
                        }
                    } else if (strtolower($element['operator']) == "!=") {
                        if (strtolower($value) == "null") {
                            $query_array[$element['querySegmentID']] = "_exists_:" . $attribute;
                        } else {
                            if (is_numeric($value)) {
                                $value = str_replace('-', '\-', $value); // Escape
                                $value = str_replace('+', '\+', $value); // Escape
                                $query_array[$element['querySegmentID']] = $attribute . "_d:(" . "<" . $value . " OR >" . $value . ")";
                            }
                        }
                    } else { // Else it must be a numeric comparison >,<,>=,<=
                        if (is_numeric($value)) {
                            $value = str_replace('-', '\-', $value); // Escape
                            $value = str_replace('+', '\+', $value); // Escape
                            $query_array[$element['querySegmentID']] = $attribute . "_d:" . "" . $element['operator'] . "" . $value;
                        } else { // A string value with numeric comparison shouldn't be possible as it's blocked in the query builder
                            $query_array[$element['querySegmentID']] = $attribute . ":" . " " . $element['operator'] . "" . $value;
                        }
                    }
                } else if (strtolower($element['operator']) == "is") {
                    if ($k == "sequence")
                        $query_array[$element['querySegmentID']] = ($element['molecule'] == "DNA" ? "dna_sequence:" : "protein_sequence:") . $element['sequence'];
                    else if ($k == "geneSymbol")
                        $query_array[$element['querySegmentID']] = "gene_symbol:" . $element['geneSymbol']['symbol'];
                    else if ($k == "hgvsName")
                        $query_array[$element['querySegmentID']] = "(hgvs_reference:" . $element['reference']['id'] . " AND hgvs_name:" . $element['hgvsName'] . ")";
                } else if (strtolower($element['operator']) == "is like") {
                    if ($k == "geneSymbol")
                        $query_array[$element['querySegmentID']] = "gene_symbol:*" . $element['geneSymbol']['symbol'] . "*";
                }
            }
        }

        $query_statement = $query['queryStatement'];
        //Add hashes to make sure that numbers on their own don't get replace (e.g. BRCA2 would get replaced if there's a statement ID of 2 after first initial)
        $query_statement = preg_replace('/\b(\d+)\b/', "##$1##", $query_statement);
        foreach ($query_array as $statement_id => $query_element) {
            $statement_id = "##" . $statement_id . "##";
            $query_element = "##(" . $query_element . ")##";
            $query_statement = preg_replace("/$statement_id/", "$query_element", $query_statement);
        }
        $query_statement = str_replace('##', '', $query_statement);

        $query_stmt = $query['queryStatement'];
        //Add hashes to make sure that numbers on their own don't get replace (e.g. BRCA2 would get replaced if there's a statement ID of 2 after first initial)
        $query_stmt = preg_replace('/\b(\d+)\b/', "##$1##", $query_stmt);
        foreach ($query_array as $statement_id => $query_element) {
            // only for epad
            if(strpos($query_element, "Age_\\[by_start_of_this_year\\]_d") !== FALSE)
            { 
                if(strpos($query_element, "_d:>=") !== FALSE) {
                    $splits = explode("_d:>=", $query_element);
                    $splits[1] = date("Y") - $splits[1] - 1;
                    $query_element = implode("_d:<=", $splits);
                } elseif(strpos($query_element, "_d:<=") !== FALSE) {
                    $splits = explode("_d:<=", $query_element);
                    $splits[1] = date("Y") - $splits[1] - 1;
                    $query_element = implode("_d:>=", $splits);
                } elseif(strpos($query_element, "_d:<") !== FALSE) {
                    $splits = explode("_d:<", $query_element);
                    $splits[1] = date("Y") - $splits[1] - 1;
                    $query_element = implode("_d:>", $splits);
                } elseif(strpos($query_element, "_d:>") !== FALSE) {
                    $splits = explode("_d:>", $query_element);
                    $splits[1] = date("Y") - $splits[1] - 1;
                    $query_element = implode("_d:<", $splits);
                }
            }
            
            $statement_id = "##" . $statement_id . "##";
            $query_element = "##(" . $query_element . ")##";
            $query_stmt = preg_replace("/$statement_id/", "$query_element", $query_stmt);
        }
        $query_stmt = str_replace('##', '', $query_stmt);

        $query_statement_for_display = $query_stmt;
        $query_statement_for_display = str_replace('_d:', ':', $query_statement_for_display); // Remove the appended numeric index name so that it isn't displayed to the user
        $query_statement_for_display = str_replace('_raw:', ':', $query_statement_for_display);
        $query_statement_for_display = str_replace('_missing_', 'missing', $query_statement_for_display);
        $query_statement_for_display = str_replace('_exists_', 'exists', $query_statement_for_display);
        $query_statement_for_display = str_replace('\[', '[', $query_statement_for_display);
        $query_statement_for_display = str_replace('\]', ']', $query_statement_for_display);
        print "<h4 id='query_for_disp'>$query_statement_for_display</h4>";
        return array($query_statement, $query_statement_for_display);
    }

    public function search(string $json_string, string $network_key, int $user_id) {
        $session = \Config\Services::session();
        $sourceModel = new Source($this->db);
        $networkModel = new Network($this->db);
        $elasticModel = new Elastic($this->db);

        $hosts = (array)$this->setting->settingData['elastic_url'];
        $elasticSearch = new ElasticSearch($hosts);

    	$api = json_decode($json_string, 1);

    	if(json_last_error() !== JSON_ERROR_NONE) {
    		header('Content-Type: application/json');
	        echo $this->error_codes['400'];
	        return;
		}
		
		$this->api = $api;

        $installation_key = $this->setting->settingData['installation_key'];

    	$user_id = ($session->get('user_id') != null) ? $session->get('user_id') : $user_id;

        // fetch sources for which the user has source display access       
        // Localised the function (Mehdi Mehtarizadeh 21/08/2019)
        $sdg_arr = $sourceModel->getSourcesForInstallationThatUserIdHasDisplayGroupAccessTo($user_id, $installation_key, (int)$network_key, 'source_display');

        $sdg_ids = [];
		if(!array_key_exists('error', $sdg_arr)) {
			foreach ($sdg_arr as $s) {
				$sdg_ids[$s['source_id']] = $s['source_id'];
			}
        }
        
        // fetch sources for which the user has data display access (count display group)
        //Localised the function (Mehdi Mehtarizadeh 21/08/2019)
        $cdg_arr = $sourceModel->getSourcesForInstallationThatUserIdHasDisplayGroupAccessTo($user_id, $installation_key, (int)$network_key, 'count_display');
        $cdg_ids = [];
		if(!array_key_exists('error', $cdg_arr)) {
			foreach ($cdg_arr as $s) {
				$cdg_ids[$s['source_id']] = $s['source_id'];
			}
		}		

		// Fetch all source id part of the network for this installation
        $network_sources = $networkModel->getSourcesForNetworkPartOfGroup($installation_key, $network_key);

        $sources = $sourceModel->getSources('source_id, name, description', array('type !=' => 'federated')); // this and the above needs replacing with a sources model function to get all the data from local databases (need greg's new code)
		//end fetching sources

		if(!isset($api['logic']) || empty($api['logic'])) {
			$this->makeLogic($api); // no logic section provided
		}
		
		$result = $this->decouple($api['logic']); // convert to ORs(AND, AND)
		error_log("Decouple result : ".$result);

		$pointer_query = $this->generate_pointer_query($result, $api);

		// $es5_query = $this->generate_es5_query($result, $api);
        $es5_counts = [];
		foreach ($sources as $source_array) {

            $elasticIndexName = $elasticModel->getTitlePrefix() . "_" . $source_array['source_id'];
            if ($elasticSearch->indexExists($elasticIndexName)) {
                $source_id = $source_array['source_id'];
                if(!in_array($source_id, $network_sources)) continue; //as above
                $es5_counts[$source_array['name']] = "Access Denied"; // not sure if this correct, yes if they are not in sg then access denied, but if in should get counts
    
                if (array_key_exists($source_id, $sdg_ids))
                {
                    $es5_counts[$source_array['name']] = $this->process_query($source_array['name'], $pointer_query);
                    // $es5_counts[$source_array['name']] = count($this->es5v2_records($source_array['name'], $pointer_query));
                    // $es5_counts[$source_array['name']] = count($this->es5v2_records($source_array['name'], $pointer_query));
                }
                if (array_key_exists($source_id, $cdg_ids)) {  //don't need to be in cdg to just get counts
                    // add code to return data or link for sources where user has access
                }
            }
		}

		return json_encode($es5_counts);
    }

    public function process_query($source, $pointer_array) {
        $sourceModel = new Source($this->db);

        $sourceId = $sourceModel->getSourceIDByName($source);

        // $notop = "/value:-/"; //needed to detech a NOT query
		$element = [];
    	$orarray = explode(") OR (", $pointer_array); //given that we convert all querys into a series of ors we can start by splitting them. (A and (B or c) = (A and B) or (A and C))
    	
    	// first step is to create counts for each core component search (the bits in '[]') so we know which order to collect IDs as this will reduce memory use or scan and scroll in ES
		$numor = 0; // which or we are processing, start at first element in array
		error_log("Getting Counts");
    	foreach ($orarray as $and) { //each or element should be an and statement note that we are using brackets to distinguish elements that need to be queried together, i.e [chr:1 and pos:124]
		    $andarray = explode("] AND [", $and); //create array of '[]' elements
		    foreach ($andarray as $pointer) { // here we are going to query each '[]' and keep counts for each one.
		    	// error_log("Pointer: $pointer");
		        //remove any parantheses or brackets
    		    $pointer = trim($pointer, "()[]/");
				$type = explode('/', $pointer)[2];
				$lookup = $this->getVal($this->api, $pointer);
				$element[$numor]["$pointer"] = $this->component_switch($type,$lookup,$source,TRUE);
		    }
		    $numor++;
		}

		error_log(print_r($element, 1));
		error_log("getting IDs");
        $outids = []; // final output of ids that match query, return count of this.
		foreach ($element as $current) {
            $noids = 0;
			asort($current); //sort the counts in an or statement so only need to keep array of smallest number of ids
		    if (reset($current) == 0) continue; // if smallest is 0 then no need to continue as answer is 0
		    $andids=[]; //array of ids for current or statement

			foreach ($current as $pointer => $val){
                if ($noids == 1) {
                    break;
                }
				$lookup = $this->getVal($this->api, $pointer);
				$type = explode('/', $pointer)[2];

				if (array_key_exists('operator',$lookup) === FALSE || (substr($lookup['operator'],0,6) !== 'is not' && $lookup['operator'] !== '!=')){
					error_log("IS");
					$ids = $this->component_switch($type,$lookup,$source,FALSE);
					
					if (count($andids) > 0){
                        $andids = array_intersect($andids, $ids);
                        if (count($andids) == 0){
                            $noids = 1;
                        }					
                    }
                    else{
						$andids = $ids;
					}					
				}
			}

			foreach ($current as $pointer => $val){
                if ($noids == 1) {
                    break;
                }
				$type = explode('/', $pointer)[2];

				$lookup = $this->getVal($this->api, $pointer);
				if (array_key_exists('operator',$lookup) === TRUE && (substr($lookup['operator'],0,6) === 'is not' || $lookup['operator'] === '!=')){
					error_log("IS NOT");
					$ids = $this->component_switch($type,$lookup,$source,FALSE);

					if (count($andids) > 0){
                        $andids = array_values(array_diff($andids, $ids));
                        if (count($andids) == 0){
                            $noids = 1;
                        }
					}else{
                        $eavModel = new EAV($this->db);

                        $uniqueSubjectIdsArray = $eavModel->getEAVs('subject_id', ['source'=> $sourceId, 'elastic' => 1], true);	
                        $uniqueSubjectIds = [];
                        foreach ($uniqueSubjectIdsArray as $uid) {
                            array_push($uniqueSubjectIds, $uid['subject_id']);
                        }		
						$andids = array_values(array_diff($uniqueSubjectIds, $ids));
					}
				}
			}


		    $outids = array_unique(array_merge($outids,$andids), SORT_REGULAR);
		    
		}
		return $outids;
    }
    
    public function eav_query($lookup,$source, $iscount = TRUE){
        $elasticModel = new Elastic($this->db);
        $sourceModel = new Source($this->db);
        
		// $lookup = $this->getVal($this->api, $pointer);
		// if ($lookup['operator'] === '!=') $lookup['operator'] = 'is not';
		$isnot = ($iscount == TRUE && (substr($lookup['operator'],0,6) === 'is not' || $lookup['operator'] === '!=')) ? TRUE : FALSE; 

        $paramsnew = [];
        $sourceId = $sourceModel->getSourceIDByName($source);
        $es_index = $elasticModel->getTitlePrefix() . "_" . $sourceId;
        
		//$paramsnew = ['index' => $es_index, 'size' => 0, 'type' => 'subject'];
		$paramsnew = ['index' => $es_index];
        $paramsnew['body']['query']['bool']['must'][0]['term']['source'] = $source . "_eav"; // for source

		$tmp[]['match'] = ['attribute' => $lookup['attribute']];

		switch($lookup['operator']) {
			case 'is': case '=': case 'is not': case '!=':
				$tmp[]['match'] = ['value.raw' => $lookup['value']];
				break;
			case 'is like': case 'is not like':
				$tmp[]['wildcard'] = ['value.raw' => $lookup['value']];
				break;
			case '>':
				$tmp[]['range'] = ['value.d' => [ 'gt' => $lookup['value']]];
				break;
			case '>=':
				$tmp[]['range'] = ['value.d' => [ 'gte' => $lookup['value']]];
				break;
			case '<':
				$tmp[]['range'] = ['value.d' => [ 'lt' => $lookup['value']]];
				break;
			case '<=':
				$tmp[]['range'] = ['value.d' => [ 'lte' => $lookup['value']]];
				break;

		}	
		$arr = [];		
		$arr['has_child']['type'] = 'eav';
		$arr['has_child']['query']['bool']['must'] = $tmp;	
		$paramsnew['body']['query']['bool']['must'][1]['bool']['must'] = $arr;
		$paramsnew['body']['aggs']['punique']['terms']=['field'=>'subject_id','size'=>10000]; //NEW

		$esquery = $this->elasticClient->search($paramsnew);

        if ($iscount){
            $result = $esquery['hits']['total'] > 0 && count($esquery['aggregations']['punique']['buckets']) > 0 ? count($esquery['aggregations']['punique']['buckets']) : 0;
        }
        else{
            $result = array_column($esquery['aggregations']['punique']['buckets'], 'key');
        }
		if ($isnot){ 
            $eavModel = new EAV($this->db);
            $uniqueSubjectIdsArray = $eavModel->getEAVs('subject_id', ['source'=> $sourceId, 'elastic' => 1], true);	
            $uniqueSubjectIds = [];
            foreach ($uniqueSubjectIdsArray as $uid) {
                array_push($uniqueSubjectIds, $uid['subject_id']);
            }	
            
            $all_ids = ($iscount==TRUE) ? count($uniqueSubjectIds) : $uniqueSubjectIds;
            $result = $iscount==TRUE ? $all_ids - $result : array_diff($all_ids,$result) ;

        }

        return $result;
	}

    public function phenotype_query($lookup,$source,$iscount = TRUE){
        $elasticModel = new Elastic($this->db);
        $sourceModel = new Source($this->db);

		error_log("PHENOTYPE QUERY");

		$isnot = ($iscount == TRUE && substr($lookup['operator'],0,6) === 'is not') ? TRUE : FALSE; 

		switch($lookup['operator']) {
			case 'is': case 'is not': case '=':
				$tmp[]['match'] = ['value.raw' => strtolower($lookup['value'])];
				break;
			case 'is like': case 'is not like':
				$tmp[]['wildcard'] = ['value.raw' => strtolower($lookup['value'])];
				break;
        }

        $paramsnew = [];
        $sourceId = $sourceModel->getSourceIDByName($source);
        $es_index = $elasticModel->getTitlePrefix() . "_" . $sourceId;
        
		// Elasticsearch query
        //$paramsnew = ['index' => $es_index, 'size' => 0, 'type' => 'subject'];
        $paramsnew = ['index' => $es_index];

		$paramsnew['body']['query']['bool']['must'][0]['term']['source'] = $source . "_eav"; // for source
		$paramsnew['body']['query']['bool']['must'][1]['has_child']['type'] = 'eav';
		$paramsnew['body']['query']['bool']['must'][1]['has_child']['query']['bool']['must'] = $tmp;

		$negop = 'must_not';
		
		if (array_key_exists('negated',$lookup) && $lookup['negated'] == 'True') $negop = 'must'; //need to add negated to phenotype component
		error_log($negop);
		$paramsnew['body']['query']['bool'][$negop][0]['has_child']['type'] = 'eav';
		$paramsnew['body']['query']['bool'][$negop][0]['has_child']['query']['bool']['must'][0]['match']['attribute'] = 'negated';
		$paramsnew['body']['query']['bool'][$negop][0]['has_child']['query']['bool']['must'][1]['match']['value'] = '1';

		$paramsnew['body']['aggs']['punique']['terms']=['field'=>'subject_id','size'=>10000]; //NEW
		error_log(json_encode($paramsnew));
		$esquery = $this->elasticClient->search($paramsnew);
		//error_log("es5_count: " . json_encode($esquery));
		if ($iscount) $result = $esquery['hits']['total'] > 0 && count($esquery['aggregations']['punique']['buckets']) > 0 ? count($esquery['aggregations']['punique']['buckets']) : 0;
		else $result = array_column($esquery['aggregations']['punique']['buckets'], 'key');
		if ($isnot){ 
            $eavModel = new EAV($this->db);
            $uniqueSubjectIdsArray = $eavModel->getEAVs('subject_id', ['source'=> $sourceId, 'elastic' => 1], true);	
            $uniqueSubjectIds = [];
            foreach ($uniqueSubjectIdsArray as $uid) {
                array_push($uniqueSubjectIds, $uid['subject_id']);
            }	
            $all_ids = ($iscount==TRUE) ? count($uniqueSubjectIds) : $uniqueSubjectIds;
            $result = $iscount==TRUE ? $all_ids - $result : array_diff($all_ids,$result) ;
		}
		// end ES

        return $result;
		

    }
    
    public function sim_query($lookup,$source, $count = TRUE){
        
        $neo4jUsername = $this->setting->settingData['neo4j_username'];
        $neo4jPassword = $this->setting->settingData['neo4j_password'];
        $neo4jAddress = $this->setting->settingData['neo4j_server'];
        $neo4jPort = $this->setting->settingData['neo4j_port'];

        $baseNeo4jAddress = $neo4jAddress;
        if (strpos($baseNeo4jAddress, 'http://') !== false) {
            $baseNeo4jAddress = str_replace("http://","",$baseNeo4jAddress);
        }
        if (strpos($baseNeo4jAddress, 'https://') !== false) {
            $baseNeo4jAddress = str_replace("https://","",$baseNeo4jAddress);
        }

        $neo4jClient =  \GraphAware\Neo4j\Client\ClientBuilder::create()
        ->addConnection('default', 'http://'. $neo4jUsername . ':' .$neo4jPassword .'@'.$baseNeo4jAddress.':'.$neo4jPort)
        ->setDefaultTimeout(60)
        ->build();	

		if (array_key_exists('r',$lookup)){
            $r = $lookup['r'];
            $s = $lookup['s'];
            $id_str = '';
			foreach ($lookup['ids'] as $id) {$id_str .= "n.hpoid=\"" . $id . "\" or "; }
			$id_str = trim($id_str, ' or ');
            
            //Do not remove the below line. It's the older way of retrieving similarity data from Neo4J
            //$neo_query = "MATCH (n:HPOterm)-[:REPLACED_BY*0..1]->()-[:SIM_AS*0..10]->()-[r:SIMILARITY]-()<-[:SIM_AS*0..10]-()<-[:REPLACED_BY*0..1]-()-[r2:PHENOTYPE_OF]->(m) where r.rel > $r and m.source = \"" . $source . "\" and (" . $id_str . " ) with m.omimid as omimid, m.subjectid as subjectid, max(r.rel) as maxicm, n.hpoid as hpoid with omimid as omimid, subjectid as subjectid, sum(maxicm) as summax where summax > $s return omimid, subjectid, summax ORDER BY summax DESC";
        
            //The following way matches the new query builder UI
            if ($r == 1) {
                $neo_query = "MATCH (n:HPOterm)-[:REPLACED_BY*0..1]-()-[r2:PHENOTYPE_OF]->(m) where (" . $id_str . ") and m.source = \"" . $source . "\" with m.subjectid as subjectid, n.hpoid as hpoid with subjectid as subjectid, count(hpoid) as hpoid where hpoid >=  $s  return subjectid, hpoid";
            }
            else {
                $neo_query = "MATCH (n:HPOterm)-[:REPLACED_BY*0..1]->()-[:SIM_AS*0..10]->()-[r:SIMILARITY]-()<-[:SIM_AS*0..10]-()<-[:REPLACED_BY*0..1]-()-[r2:PHENOTYPE_OF]->(m) where r.rel >  $r  and (" . $id_str . ") and m.source = \"" . $source . "\" with m.omimid as omimid, m.subjectid as subjectid, max(r.rel) as maxicm, count(distinct(n.hpoid)) as hpoid where hpoid >=  $s with hpoid as hpoid, omimid as omimid, subjectid as subjectid, sum(maxicm) as summax where summax > 0 return omimid, subjectid, hpoid ORDER BY hpoid DESC";
            }

            $result = $neo4jClient->run($neo_query);

            $records = $result->getRecords();

            if($count === true) {
                return count($records);
            } else {
                $pat_ids = [];
                foreach ($records as $record) {
                    $pat_ids[] = $record->value('subjectid');
                }
                return $pat_ids;
            }
        }

		// if (array_key_exists('r',$lookup)){	} // add code for jaccard	

	}

    public function sv_query($lookup,$source, $iscount = TRUE){
        $elasticModel = new Elastic($this->db);
        $sourceModel = new Source($this->db);

		error_log("SUBJECT VARIANT QUERY");

        $arr = [];
		foreach ($lookup as $key => $value) { // replace with actual parameters
			$tmp = [];
			$tmp[]['match'] = ['attribute' => $key];
			$tmp[]['match'] = ['value.raw' => $value];
			$arr_child['has_child']['type'] = 'eav';

			$arr_child['has_child']['query']['bool']['must'] = $tmp;
			$arr[] = $arr_child;
		}

        $paramsnew = [];
        $sourceId = $sourceModel->getSourceIDByName($source);
        $es_index = $elasticModel->getTitlePrefix() . "_" . $sourceId;

		$paramsnew = ['index' => $es_index, 'size' => 0];
        $paramsnew['body']['query']['bool']['must'][0]['term']['source'] = $source . "_eav"; // for source
		$paramsnew['body']['query']['bool']['must'][1]['bool']['must'] = $arr;
		$paramsnew['body']['aggs']['punique']['terms']=['field'=>'subject_id','size'=>10000]; //NEW

        $esquery = $this->elasticClient->search($paramsnew);
        $json_query = json_encode($paramsnew);
        if ($iscount){
            $result = $esquery['hits']['total'] > 0 && count($esquery['aggregations']['punique']['buckets']) > 0 ? count($esquery['aggregations']['punique']['buckets']) : 0;
        }
        else{
            $result = array_column($esquery['aggregations']['punique']['buckets'], 'key');
        }

        return $result;
    }
    
    public function makeLogic(&$api) {

		$api['logic'] = ['-AND' => []];
		foreach ($api['query']['components'] as $component => $arr) {
			if(!empty($arr)) {
				$cnt = 0;
				foreach ($arr as $entry)
					array_push($api['logic']['-AND'], "/query/components/$component/" . $cnt++);
			}
		}
    }
    
    function decouple($arr, $type = false) {
		reset($arr); $type = key($arr); 
		if($type !== 0) {
			$arr = $arr[$type];
		}
		$str = '';
		foreach ($arr as $key => $el) {
			if(is_array($el)) {
				if($this->is_assoc($el)) {
					$out = $this->decouple($el);
					if($type === '-AND') {
						$str = $this->myMerge($this->mySplit($str), $this->mySplit($out));
					} else {
						$str = implode(',', array_merge($this->mySplit($str), $this->mySplit($out)));
					}
				} else {
					return implode($type === '-AND' ? '' : ',', $el);
				}
			} else {
				if($type === '-AND') {
					$str = $this->myMerge($this->mySplit($str), [$el]);
				} else {
					$str = implode(',', array_merge($this->mySplit($str), [$el]));
				}
			}
		}
		return trim($str, ',');
    }
    
    public function generate_pointer_query($result, $jex) {
    	$qStr = '';
		foreach (explode(',', $result) as $expOR) {
		    $oStr = '';
			foreach (explode('|', $expOR) as $expAND) {
				$aStr = $expAND;
		        $aStr = '[' . trim($aStr, ' AND ') . ']';
		        $oStr .= $aStr . ' AND ';
		   }
		   $oStr = trim($oStr, ' AND ');
		   $qStr .= "($oStr) OR ";
		}
		return trim($qStr, ' OR ');
    }
    
    function mySplit($str) {return preg_split('/,/',$str, NULL, PREG_SPLIT_NO_EMPTY);}

    function myMerge($arr1, $arr2) {
		if(empty($arr1)) $arr = $arr2;
		elseif(empty($arr2)) $arr = $arr1;
		else foreach($arr1 as $a1) foreach($arr2 as $a2) $arr[] = $a1 . '|' . $a2;
		return implode(',', $arr);
    }
    
    function is_assoc($arr) {foreach ($arr as $key => $val) if(is_array($val)) return true; return false;}

    function getVal($jex, $path) {
		$pArr = explode('/', ltrim($path, '/'));
        $path = "['" . implode("']['", $pArr) . "']";
	    return eval("return \$jex{$path};");
	}

    public function component_switch($type,$lookup,$source,$iscount){
		if ($type == "phenotype") return $this->phenotype_query($lookup, $source,$iscount);
		elseif ($type == "sim") return $this->sim_query($lookup, $source, $iscount);
		elseif ($type == "eav") return $this->eav_query($lookup, $source, $iscount);
		elseif ($type == "subjectVariant") return $this->sv_query($lookup, $source, $iscount);

	}
}