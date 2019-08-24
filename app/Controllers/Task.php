<?php namespace App\Controllers;

/**
 * Task.php
 * Created 02/08/2019
 * 
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 * Formerly known as sqlinsert.php
 * 
 * This is controller is only accessible via the CLI.
 * It implements tasks that need to be run in the background.
 * 
 */

use CodeIgniter\Controller;
use CodeIgniter\CLI\CLI;
use App\Models\Upload;
use App\Models\Source;
use App\Models\Elastic;
use App\Models\Settings;
use App\Models\EAV;
use App\Models\Neo4j;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

 class Task extends Controller{

    function __construct(){
        $this->db = \Config\Database::connect();
		$this->setting =  Settings::getInstance($this->db);
    }

    /**
     * Pheno Packet Insert - manages the loop to insert all recently uploaded json files sequentially 
     * into mysql for the given source.
     *
     * @param string $source - Name of source update should be performed for
     * @return N/A
     */
    public function phenoPacketInsert($source_id) {
        error_log("PhenoPacket");
        
        $uploadModel = new Upload($this->db);
        $sourceModel = new Source($this->db);

        // get a list of json files just uploaded to this source
        $files = $uploadModel->phenoPacketFiles($source_id);
        // error_log("files");
        // error_log(print_r($files,1));
        for ($t=0; $t < count($files); $t++) {
            $file = $files[$t]['FileName'];
            $file_id = $files[$t]['ID'];
            error_log("Now doing: ".$file);
            // create array of the given json file
            $data = json_decode(file_get_contents(FCPATH . "upload/UploadData/".$source_id."/json/".$file), true);
            $uploadModel->phenoPacketClear($source_id,$file_id);
            $uploadModel->clearErrorForFile($file_id);
            $meta = null;
            if (array_key_exists("metaData", $data)) {
                $meta = [];
                $target = &$data['metaData']['resources'];
                for ($i=0; $i < count($target); $i++) { 
                    $meta[$target[$i]['namespacePrefix']] = [];
                    $meta[$target[$i]['namespacePrefix']]['meta_name'] = $target[$i]['name'];
                    $meta[$target[$i]['namespacePrefix']]['meta_version'] = $target[$i]['version'];
                }		
                
            }
            preg_match("/(.*)\./", $file, $matches);
            $data['id'] = $matches[1];
            $id = $data['id'];
            
            $this->db->transStart();			
            // perform recursive insert for given file
            $done = $this->recursivePacket($data,$meta,$id,$file_id,$source_id,null,null,null, null);
            if (!$done['negated']['true'] && !empty($done['negated']['uid'])) {
                $uploadModel->jsonInsert($done['negated']['uid'],$source_id,$file_id,$id,'negated',0);	
            }
            $output = [];
            $hits = [];
            $matches = array_unique($this->arrSearch($data,$output));
            $matches = $uploadModel->checkNegatedForHPO($matches,$file_id);
            for ($i=0; $i < count($matches); $i++) { 
                $result = json_decode($this->ancestorCurl($matches[$i]),1);
                if (array_key_exists('ancestor', $result)) {
                    foreach ($result['ancestor'] as $key => $value) {
                        if (!array_key_exists($value['id'], $hits)) {
                            $hits[$value['id']] = $value['label'];
                        }
                    }
                }						
            }
            $uid = md5(uniqid(rand(),true));	
            $uploadModel->jsonInsert($uid,$source_id,$file_id,$id,'type','ancestor');
            foreach ($hits as $key => $value) {
                $uploadModel->jsonInsert($uid,$source_id,$file_id,$id,'ancestor_hpo_id',$key);
                $uploadModel->jsonInsert($uid,$source_id,$file_id,$id,'ancestor_hpo_label',$value);
            }
            $dbRet = $this->db->transComplete();
            if ($this->db->transStatus() === FALSE) {
                $error = true;
                error_log("failed");
                error_log($this->db->transStatus());
                $message = "Data Failed to insert. Please double check file for sanity.";
                $error_code = 4;
                $uploadModel->errorInsert($file,$source,$message,$error_code,true);
            }
            // update status table to class current file as inserted to database
            $uploadModel->insertStatistics($file_id, $source_id);
            $uploadModel->bigInsertWrap($file_id, $source_id);
        }
        // we have finished updating the source and unlock it so further uploads and updates can be performed
        $sourceModel->toggleSourceLock($source_id);	
    }

    /**
     * VCF Elastic - Insert into ElasticSearch VCF Files
     *
     * Imported from elastic controller by Mehdi Mehtarizadeh (06/08/2019)
     * 
     * @param int $source_id - The source we are inserting into
     * @return N/A
     */
    public function vcfElastic($source_id) {

        $hosts = array($this->setting->settingData['elastic_url']);
        $elasticClient =  \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();

        error_log("vcfElastic");

        $uploadModel = new Upload($this->db);
        $elasticModel = new Elastic($this->db);
        $sourceModel = new Source($this->db);

        // Get Pending VCF Files
        $vcf = $elasticModel->getvcfPending($source_id);

        $title = $elasticModel->getTitlePrefix();

        for ($t=0; $t < count($vcf); $t++) { 
            error_log("now doing ".$vcf[$t]['FileName']);
            $index_name = $title."_".$source_id."_".strtolower($vcf[$t]['patient'])."";
            if ($vcf[$t]['tissue']) {
                $index_name = $index_name."_".strtolower($vcf[$t]['tissue']);
            }
             error_log("Index name: ".$index_name);
            $params['index'] = $index_name;
            // return;
            if ($elasticClient->indices()->exists($params)){
                $params = ['index' => $index_name];
                $response = $elasticClient->indices()->delete($params);
            }
            $params = [];
            $params['index'] = $index_name;
            $map = '{
                "settings":{ },
                "mappings":{
                    "subject":{
                        "properties":{
                            "eav_rel":{"type": "join", "relations": {"sub":"eav"}},
                            "type": {"type": "keyword"},
                            "subject_id": {"type": "keyword"},
                            "patient_id": {"type": "keyword"},
                            "file_name": {"type": "keyword"},
                            "source": {"type":"keyword"},
                            "attribute":{"type":"keyword"},
                            "value":{"type":"text", "fields": {"raw":{"type": "keyword"}, "d":{"type": "long", "ignore_malformed": "true"}, "dt":{"type": "date", "ignore_malformed": "true"}}}                              
                        }
                    }
                }
            }'; 
            $map2 = json_decode($map,1);
            $params['body'] = $map2;		  
            error_log("params: ".var_dump($params));
            $response = $elasticClient->index($params);
            $source_name = $sourceModel->getSourceNameByID($source_id);
            
            // Open file for reading
            $handle = fopen(FCPATH."upload/UploadData/".$source_id."/".$vcf[$t]['FileName'], "r");
            // The list of extra parameters we want to include in our insert
            $config = ["AF"];
            $headers = [];
            $counter = 0;
            if ($handle) {
                // Read file line by line
                while (($line = fgets($handle)) !== false) {
                    // Ignore all lines which start with ##
                    if (preg_match("/^##/", $line)) {
                        continue;
                    }
                    // This line has all the headers listed on it
                    else if (preg_match("/^#/", $line)) {
                        $line = substr($line, 1);
                        $headers = explode("\t", $line);
                    }
                    // We have reached the data
                    else {
                        $patient = $vcf[$t]['patient'];
                        // Each row is its own group so we need to create a link id 
                        // Explode our lines by tabs
                        $values = explode("\t", $line);
                        $link = md5(uniqid());
                        // create parent document
                        $bulk['body'][] = ["index"=>["_index"=>$index_name, "_type"=>"subject","_id"=>$link]];
                        $bulk['body'][] = ["record_id"=>$patient, "patient_id"=>$patient, "eav_rel"=>["name"=>"sub"], "type"=>"subject", "source"=>$source_name."_vcf"];
                        $counter++;
                        // Every thousand documents perform a bulk operation to ElasticSearch
                        if ($counter%1000 == 0) {      
                            $responses = $elasticClient->bulk($bulk);			  
                            $bulk=[];
                            unset ($responses);
                        }
                        for ($i=0; $i < 8; $i++) { 
                            if ($i == 7) {
                                // go through format column and multidimensional array with each index
                                // having two elements: [0] for alias and [1] for the value
                                $string = $values[$i];
                                $val = array_map(function($string) { return explode('=', $string); }, explode(';', $string));
                                foreach ($val as $v) {
                                    if (in_array($v[0], $config)) {
                                        $id = md5(uniqid());
                                        $bulk['body'][] = ["index"=>["_index"=>$index_name,"_type"=>"subject", "routing"=>$link,"_id"=>$id]];
                                        $bulk['body'][] = ["record_id"=>$values[2], "patient_id"=> $patient,"attribute"=>$v[0],"value"=>$v[1], "eav_rel"=>["name"=>"eav","parent"=>$link], "type"=>"eav", "source"=>$source_name."_vcf"];
                                        $counter++;	
                                        if ($counter%1000 == 0) {      
                                            error_log($counter);          	
                                            $responses = $elasticClient->bulk($bulk);
                                            
                                            $bulk=[];
                                            unset ($responses);
                                        }
                                    }
                                }
                            } 
                            else if ($i == 6) {
                                continue;
                            }		              
                            else {
                                $id = md5(uniqid());
                                $bulk['body'][] = ["index"=>["_index"=>$index_name,"_type"=>"subject", "routing"=>$link,"_id"=>$id]];
                                $bulk['body'][] = ["record_id"=>$patient, "patient_id"=> $patient,"attribute"=>$headers[$i],"value"=>$values[$i], "eav_rel"=>["name"=>"eav","parent"=>$link], "type"=>"eav", "source"=>$source_name."_vcf"];
                                $counter++;		
                                if ($counter%1000 == 0) {      
                                    error_log($counter);          	
                                    $responses = $elasticClient->bulk($bulk);
                                    
                                    $bulk=[];
                                    unset ($responses);
                                }
                            }
                        }	                    
                    }
                }
                fclose($handle);
                    // Finished all files. Send the last records through
                $responses = $elasticClient->bulk($bulk);
                // error_log($counter);
                unset ($responses);
                unset($params);
                $bulk=[];  	
                $elasticModel->vcfWrap($vcf[$t]['FileName'],$source_id);
            } else {
                // error opening the file.
            } 

        }	
        error_log("toggling source lock on: ".$source_id);
        $sourceModel->toggleSourceLock($source_id); 		      
    }


    /**
     * bulkUploadInsert - Loop through CSV/XLSX/ODS files with spout to add to eavs table
     *
     * @param string $file        - The File We are uploading
     * @param int $delete         - 0: We do not need to delete data from eavs | 1: We do need to 
     * @param string $source      - The name of the source we are uploading to
     * @return array $return_data - Basic information on the status of the upload
     */
    public function bulkUploadInsert($file, $delete, $source_id) {

        $uploadModel = new Upload($this->db);
        $sourceModel = new Source($this->db);

        $error = array('subject_id' => 'No subject_id column.',
        'wrong_type' => 'File did not conform to allowed types.',
         'insert_fail' => 'Data Failed to insert. Please double check file for sanity.',
         'variant_invalid' => 'Variant is not valid, as according to VariantValidator. https://variantvalidator.org/',
        );
        $fileId = $uploadModel->getFileId($source_id, $file);
        $uploadModel->clearErrorForFile($fileId);
        if ($delete == 1) {		
            $sourceModel->deleteSourceFromEAVs($source_id);
        }
        list( $true, $linerow, $counter, $groupnumber, $filePath ) = array( true, 1, 0, 0, FCPATH."upload/UploadData/".$source_id."/".$file);
        
        
        $return_data = array('result_flag' => 1);   
        $attgroups = [];
        if (preg_match("/\.csv$|\.tsv$/", $file)) {
            $line = fgets(fopen($filePath, 'r'));
            if (!preg_match("/^subject_id(.)/", $line, $matches)) {
                $return_data['result_flag'] = 0;
                $return_data['error'] = $error['subject_id'];
                $message = $error['subject_id'];
                $error_code = 1;
                $uploadModel->errorInsert($fileId,$source_id,$message,$error_code,true);
                return $return_data;
            }
            else {
                $delimiter = $matches[1];
            }
            $reader = \Box\Spout\Reader\ReaderFactory::create(\Box\Spout\Common\Type::CSV);
            $reader->setFieldDelimiter($delimiter);
        }     
        elseif (preg_match("/\.xlsx$/", $file)) {

            $reader = \Box\Spout\Reader\ReaderFactory::create(\Box\Spout\Common\Type::XLSX);
        } 
        elseif (preg_match("/\.ods$/", $file)) {
            $reader = \Box\Spout\Reader\ReaderFactory::create(\Box\Spout\Common\Type::ODS);
        }
        else {
            $return_data['result_flag'] = 0;
            $return_data['error'] = "File did not conform to allowed types";   
            $message = "File did not conform to allowed types.";
            $error_code = 2;
            $uploadModel->errorInsert($fileId,$message,$error_code,true);         
            return $return_data;
        }

        $sourceModel->toggleSourceLock($source_id);	
        $reader->open($filePath);
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                if ($true) {			 
                    for ($i=0; $i < count($row); $i++) { 	
                        if ($i === 0) {
                            if ($row[$i] != "subject_id"){
                                $return_data['result_flag'] = 0;
                                $return_data['error'] = "No subject_id column.";
                                $message = "No subject_id column.";
                                $error_code = 1;
                                $uploadModel->errorInsert($fileId,$source_id,$message,$error_code,true);
                                $sourceModel->toggleSourceLock($source_id);
                                return $return_data;
                            }
                            continue;
                        }                    
                        if ($row[$i] == "<group_end>"){
                            if (!empty($temphash)){
                                $attgroups[$groupnumber] = $temphash;
                                $temphash = [];
                                $groupnumber++;
                            }
                        } 
                        else {
                            $temphash[$row[$i]] = $i;
                        }                      
                        if (!empty($temphash)){
                            $attgroups[$groupnumber] = $temphash;
                        }	
                    }
                    $true = false;
                    $this->db->transStart();			
                }
                else {
                    $subject_id = $row[0];
                    if ($subject_id == ""){
                        $point = $row;
                        $return_data['result_flag'] = 0;
                        $return_data['status'] = "error";
                        $return_data['error'] = "All records require a record ID, a record on line:".$linerow." in the import data that do not have a record ID, please add record IDs to all records and re-try the import.";
                        $message = "All records require a record ID, a record on line:".$linerow." in the import data that do not have a record ID, please add record IDs to all records and re-try the import.";
                        $error_code = 3;
                        $uploadModel->errorInsert($fileId,$source_id,$message,$error_code,true);
                        $sourceModel->toggleSourceLock($source_id);
                        return $return_data;
                    }
                    foreach ($attgroups as $group){
                        $uid = md5(uniqid(rand(),true));                           
                        foreach ($group as $att => $val){                          
                            $value = $row[$val];
                            if ($value == "") continue;     
                            if (is_a($value, 'DateTime')) $value = $value->format('Y-m-d H:i:s');
                            $uploadModel->jsonInsert($uid,$source_id,$fileId,$subject_id,$att,$value);
                            $counter++;                            
                            if ($counter % 800 == 0) {
                                $error = $this->sendBatch();   
                                error_log($counter);                          
                                if ($error) {                             
                                    error_log("failed on insert");
                                    $return_data['result_flag'] = 0;
                                    $return_data['error'] = "MySQL insert was unsuccessful";

                                    $sourceModel->toggleSourceLock($source_id);
                                    return $return_data;
                                }
                            }                         
                        }
                    }
                }
            }
            $linerow++;
        }
        $reader->close();

        $this->db->transComplete();
        $uploadModel->insertStatistics($fileId, $source_id);
        $uploadModel->bigInsertWrap($fileId, $source_id);
        $uploadModel->clearErrorForFile($fileId);
        $sourceModel->toggleSourceLock($source_id);	
        return $return_data;
    }

    
    /**
     * Regenerate ElasticSearch Index - Loop through the MySQL eavs table to add to ElasticSearch
     *
     * @param int $source_id - The source we are updating ElasticSearch for
     * @param int $add            - Whether we are adding data without fully remaking the index 
     * @return N/A
     */
    public function regenerateElasticsearchIndex($source_id, $add) {
        // Begin timer and load models
        $first  = new \DateTime();
        $hosts = (array)$this->setting->settingData['elastic_url'];
        $elasticClient = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
        $sourceModel = new Source($this->db);
        $elasticModel = new Elastic($this->db);
        $uploadModel = new Upload($this->db);
        $eavModel = new EAV($this->db);
        $neo4jModel = new Neo4j($this->db);
        $source_name = $sourceModel->getSourceNameByID($source_id);

        // Get the source id of the source we are working with

        $params = [];
        // Generate ElasticSearch index name

        $title = $elasticModel->getTitlePrefix();
        
        $index_name = $title."_".$source_id;
        $params['index'] = $index_name;
        // Check whether an Index already exists
        $flag = false;
        if ($elasticClient->indices()->exists($params)){
            // If we are not adding to the index then we need to delete the current index
            if (!$add) {
                $response = $elasticClient->indices()->delete($params);
                error_log("deleted ES5 index\n");
                $flag = true;
            }      
        }
        else{
                $flag = true;
        }
        // If we need to - create a new index
        if ($flag) {
            $map = '{  
                "settings":{},
                "mappings":{
                    "subject":{
                        "properties":{
                            "eav_rel":{"type": "join", "relations": {"sub":"eav"}},
                            "type": {"type": "keyword"},
                            "subject_id": {"type": "keyword"},
                            "source": {"type":"keyword"}, 
                            "attribute":{"type":"keyword"},
                            "value":{
                                "type":"text",
                                "fields": 
                                    {"raw":{"type": "keyword"},
                                    "d":{"type": "double", "ignore_malformed": "true"},
                                    "dt":{"type": "date", "ignore_malformed": "true"}}}           
                        }
                    }
                }
            }';

            $map2 = json_decode($map,1);
            $params['body'] = $map2;

            $response = $elasticClient->index($params);
            error_log(print_r($response,1));
            error_log("created index mapping\n"); 
        }       
        // Set the elastic state of data to stale  
        $sourceModel->updateSource(["elastic_status"=>0], ["source_id" => $source_id]);

        // Get all the unique subject ids for this source
        $unique_ids = $eavModel->getEAVs('uid,subject_id', ["source"=>$source_id, "elastic"=>0], true);

        $bulk = [];
        $counta = 0;
        $countparents = 0;
        // start making all the parent documents in ElasticSearch
        foreach($unique_ids as $index_data){
            $bulk['body'][] = ["index"=>["_index"=>$index_name, "_type"=>"subject","_id"=>$index_data['uid']]];
            $bulk['body'][] = ["subject_id"=>$index_data['subject_id'], "eav_rel"=>["name"=>"sub"], "type"=>"subject", "source"=>$source_name."_eav"];    
            $countparents++;
            if ($countparents%500 == 0){
                // error_log($countparents);
                $responses = $elasticClient->bulk($bulk);
                $bulk=[];
                unset ($responses);
            }                    
        }
        // Send the last parents through who didnt get finished in loop
        if (!empty($bulk['body'])){
            error_log("final");
            $responses = $elasticClient->bulk($bulk);
            $bulk=[];
            unset ($responses);
        }
        error_log("parents indexed");
        // Figure out how many documents we need to index
        $eavsize = count($eavModel->getEAVs('uid,subject_id', ["source"=>$source_id, "elastic"=>0]));
        $bulk=[];
        // We are looping through with the use of limit to increase speed of writing
        $offset = 0;
        while ($offset < $eavsize){
            // Get our current limit chunk of data
            $eavdata = $eavModel->getEAVs(null, ["source"=>$source_id, "elastic"=>0], false, 1000, $offset);
            // Loop through our limit chunk
            foreach ($eavdata as $attribute_array){
                $attribute_array['attribute'] = preg_replace('/\s+/', '_', $attribute_array['attribute']);
                $bulk['body'][] = ["index"=>["_index"=>$index_name,
                                            "_type"=>"subject",
                                            "routing"=>$attribute_array['uid']]];
                $bulk['body'][] = ["subject_id"=>$attribute_array['subject_id'],
                                   "attribute"=>$attribute_array['attribute'],
                                   "value"=>strtolower($attribute_array['value']),
                                    "eav_rel"=>["name"=>"eav",
                                    "parent"=>$attribute_array['uid']],
                                     "type"=>"eav",
                                     "source"=>$source_name."_eav"];
                $counta++;
                // Every 500 documents bulk insert to ElasticSearch
                if ($counta%500 == 0){
                    // error_log($counta);
                    $responses = $elasticClient->bulk($bulk);
                    $bulk=[];
                    unset ($responses);
                    // error_log("inserted");
                }   
            }
            // Update our offset 
            //error_log(print_r($eavdata,1));
            $offset += 1000;
        }
        // Send the last of our documents through
        if (!empty($bulk['body'])){
            error_log("final");
            $responses = $elasticClient->bulk($bulk);
        }        
        // The update is now complete. Perform post processing reporting  
        $eavModel->retrieveUpdateNeo4j($source_id); 
        $eavModel->updateEAVs(["elastic"=>1], ['source'=> $source_id]) ;     

        $sourceModel->toggleSourceLock($source_id);	

        if(file_exists("resources/elastic_search_status_incomplete"))
                rename("resources/elastic_search_status_incomplete", "resources/elastic_search_status_complete");
        echo "Completed";
        error_log("Completed ES5 $index_name");   
        // Determine how long it took
        $second = new \DateTime();	
        $diff = $first->diff( $second );
        error_log("For " .$eavsize. "MySQL rows it took: ". $diff->format( '%H:%I:%S' )); // -> 00:25:25     	   
    }


    /**
     * Recursive Packet 2 - Up to Date Recursive Loop to iterate through a multi nested 
     * array created from json_decoding a phenoPacket and preparing arrays
     * to insert into mysql - Table eavs
     *
     * @param array $array       - The array we are currently iterating through
     * @param array $meta        - The list of metaData tags,versions and names listed in the phenoPacket
     * @param string $id         - The subject ID for the phenoPacket
     * @param int $file          - The ID of the file we have generated this data from - UploadDataStatus
     * @param int $source        - The ID of the source we are linking this data to - sources
     * @param string $uid        - The md5 ID linking groups together
     * @param string $type       - The Type of the current nested array
     * @param boolean $one_group - To determine whether all nested array from here must remain in the same group
     * @param array $done        - Supplementary information to ensure we do not duplicate meta rows and 
     *							   correctly add negated 0 if necessary
        * @return array $done       - See above
    */
    public function recursivePacket($array,$meta,$id,$file,$source,$uid,$type,$one_group,$done) {
        $uploadModel = new Upload($this->db);

        foreach($array as $key => $value){
            if ($key == "id" && $value == $id) {
                continue;
            }
            if(is_array($value)){			
                if (is_numeric($key)) {
                    // Since we are starting a new group we need to check if the previous group had a 
                    // negated added to it. If it hasnt we need to add negated 0
                    // However we also need to make sure that the uid is present as this could be the 
                    // very first group being created
                    if (!$done['negated']['true'] && !empty($done['negated']['uid'])) {
                        $uploadModel->jsonInsert($done['negated']['uid'],$source,$file,$id,'negated',0);	
                    }
                    $uid = md5(uniqid(rand(),true));
                    // Since this is an array key all sub arrays remain part of the same group
                    // One group is set to true to ensure this
                    $one_group = true;
                    
                    // reset done array to keep track of whether negated has been added and which meta rows have been added
                    $done = ['meta' => [],'negated' => ['true' => 0],'cell' => [], "type" => []];
                    if (!in_array($type, $done['type'])) {
                        $uploadModel->jsonInsert($uid,$source,$file,$id,'type',$type);
                        array_push($done['type'], $type);
                    }
                    $done = $this->recursiveNumeric($value,$meta,$id,$file,$source,$uid,$type,$one_group,$done);
                    continue;				
                }
                else {
                    // We dont want to add any rows in this sub array
                    // if ($key = "evidence") {
                    // 	$one_group = true;
                    // }
                    $one_group = false;
                    if ($key == "metaData") {
                        continue;
                    }
                    if ($key != "type") {
                        $type = $key;	
                    }		      		            	
                }
                $done = $this->recursivePacket($value,$meta,$id,$file,$source,$uid,$type,$one_group,$done);
            }
            else {
                // We are making a group which wasnt in an indexed array
                if (!$one_group) {
                    if (!$done['negated']['true'] && !empty($done['negated']['uid'])) {
                        $uploadModel->jsonInsert($done['negated']['uid'],$source,$file,$id,'negated',0);	
                    }
                    $uid = md5(uniqid(rand(),true));
                    $one_group = true;
                    $done = ['meta' => [],'negated' => ['true' => 0],'cell' => [], "type" => []];
                    if (!in_array($type, $done['type'])) {
                        $uploadModel->jsonInsert($uid,$source,$file,$id,'type',$type);
                        array_push($done['type'], $type);
                    }	
                }
                // Since we are combining type with the key for attribute 
                // For negated we would just like it on its own. 
                // Also set negated true as we have indeed added a negated row
                if ($key == 'negated') {
                    $string = $key;
                    $done['negated']['true'] = 1;
                    if ($value === false) {
                        $value = 0;
                    }
                }
                else {
                    $string = $type."_".$key;
                    // $string = $key;
                }				
                $uploadModel->jsonInsert($uid,$source,$file,$id,$string,$value);  
                // Since we have just an id row we need to check its id group with our list of meta
                // attributes (if applicable)
                if ($key == "id") {
                    $prefix = explode(":", $value);
                    $prefix = $prefix[0];
                    if ($meta) {
                        if (array_key_exists($prefix, $meta)) {
                            // Since it exists now check if we have already added these meta rows before to this group
                            if (!in_array($prefix, $done['meta'])) {
                                $uploadModel->jsonInsert($uid,$source,$file,$id,'meta_name',$meta[$prefix]['meta_name']);
                                $uploadModel->jsonInsert($uid,$source,$file,$id,'meta_version',$meta[$prefix]['meta_version']);
                                array_push($done['meta'], $prefix);
                            }
                        } 
                    }	
                }
            }
        }
        // This check is here for when we travel up at least two array levels
        // This will add the negated 0 to those cases
        if (!$done['negated']['true'] && !empty($done['negated']['uid'])) {
            $done['negated']['true'] = 1;
            $uploadModel->jsonInsert($done['negated']['uid'],$source,$file,$id,'negated',0);	
        }
        // Pass the current uid in use to the negated check to the array above so
        // we know which group to add negated 0 to (if applicable)
        $done['negated']['uid'] = $uid;
        return $done;
    }

    /**
     * Array Search - Recursive loop to find all HP terms within PhenoPacket
     *
     * @param array $array    - Array we are searching
     * @param array $output   - Array we are adding results to
     * @return array $output  - See Above
     */
    function arrSearch($array,$output){
        foreach($array as $key => $value){
            if(is_array($value)){
                $output = $this->arrSearch($value,$output);
            }
            else {
                if ($key == "id") {
                    if (preg_match("/^HP:/", $value)) {
                        array_push($output, $value);
                    }
                }
            }
        }
        return $output;	   
    } 

    /**
     * Ancestor Curl - Hit Tim Beck's api to get all parents of given HPO term
     * 
     * @param string $array   - HPO term we are wanting parents for 
     * @return array $result  - Array for list of HPO terms and labels
     */
    function ancestorCurl($hpo) {
        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();

        // Temporarily disable ssl verifier to avoid the ambiguous self signed ssl error 60
        // by Mehdi Mehtarizadeh
        // 05/08/2019
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        curl_setopt($ch, CURLOPT_URL, 'https://www240.lamp.le.ac.uk/hpo/ancestor.php?id='.$hpo);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);
        return $result;
    }


    /**
     * Recursive Numeric - Inner recursive loop to only deal with numbered nested arrays
     * to ensure that the whole nested group is kept in a single uid group
     *
     * @param array $array       - The array we are currently iterating through
     * @param array $meta        - The list of metaData tags,versions and names listed in the phenoPacket
     * @param string $id         - The subject ID for the phenoPacket
     * @param int $file          - The ID of the file we have generated this data from - UploadDataStatus
     * @param int $source        - The ID of the source we are linking this data to - sources
     * @param string $uid        - The md5 ID linking groups together
     * @param string $type       - The Type of the current nested array
     * @param boolean $one_group - To determine whether all nested array from here must remain in the same group
     * @param array $done        - Supplementary information to ensure we do not duplicate meta rows and 
     *							   correctly add negated 0 if necessary
    * @return array $done       - See above
    */
    public function recursiveNumeric($array,$meta,$id,$file,$source,$uid,$type,$one_group,$done) {
        $uploadModel = new Upload($this->db);

        // prep for variants api call
        if ($type == "variants") {
            list( $assembly, $chrom, $coordinateSystem, $pos, $ref, $alt ) = array( "","","","","","");
        }
        foreach($array as $key => $value){
            if ($key == "id" && $value == $id) {
                continue;
            }
            if(is_array($value)){	
                if ($type == "variants") {
                    if (!in_array($pos, $done['cell'])) {
                        $compact = compact("assembly","chrom","coordinateSystem","pos","ref","alt","id","file","source");
                        $pos = $this->cellBaseApi($compact);
                        array_push($done['cell'], $pos);
                    }		
                }		
                if (is_numeric($key)) {			
                        $one_group = true;
                        if (!in_array($type, $done['type'])) {
                            $uploadModel->jsonInsert($uid,$source,$file,$id,'type',$type);
                            array_push($done['type'], $type);
                        }				           	
                }
                else {		        	
                    if ($key == "metaData") {
                        continue;
                    }
                    if ($key != "type") {
                        $type = $key;
                        // error_log("extra type: ".$type);
                        if ($uid) {
                            if (!in_array($type, $done['type'])) {
                                $uploadModel->jsonInsert($uid,$source,$file,$id,'type',$type);
                                array_push($done['type'], $type);
                            }	
                        }
                                
                    }
                }
                $done = $this->recursiveNumeric($value,$meta,$id,$file,$source,$uid,$type,$one_group,$done);
            }
            else {			
                if ($type == "variants") {	
                    switch($key) {
                        case 'genomeAssembly' :
                            $assembly = $value;
                            break;
                        case 'sequence' :
                            $chrom = $value;
                            break;
                        case 'coordinateSystem' :
                            $coordinateSystem = $value;
                            break;
                        case 'position' :
                            $pos = $value;
                            break;
                        case 'deletion' :
                            $ref = $value;
                            break;
                        case 'insertion' :
                            $alt = $value;
                            break;
                    }
                    // if all the variables have content, call the api for Cellbase data on variant data
                    if ($this->mempty($assembly, $chrom, $coordinateSystem, $pos, $ref, $alt)) {
                        if (!in_array($pos, $done['cell'])) {
                            $compact = compact("assembly","chrom","coordinateSystem","pos","ref","alt","id","file","source");
                            $pos = $this->cellBaseApi($compact);
                            array_push($done['cell'], $pos);
                        }		
                    }
                }
                // Since we are combining type with the key for attribute 
                // For negated we would just like it on its own. 
                // Also set negated true as we have indeed added a negated row
                if ($key == 'negated') {
                    $string = $key;
                    $done['negated']['true'] = 1;
                    if ($value === false) {
                        $value = 0;
                    }
                }
                else {
                    $string = $type."_".$key;
                    // $string = $key;
                }				
                $uploadModel->jsonInsert($uid,$source,$file,$id,$string,$value);  
                // Since we have just an id row we need to check its id group with our list of meta
                // attributes (if applicable)
                if ($key == "id") {
                    $prefix = explode(":", $value);
                    $prefix = $prefix[0];
                    if ($meta) {
                        if (array_key_exists($prefix, $meta)) {
                            // Since it exists now check if we have already added these meta rows before to this group
                            if (!in_array($prefix, $done['meta'])) {
                                $uploadModel->jsonInsert($uid,$source,$file,$id,'meta_name',$meta[$prefix]['meta_name']);
                                $uploadModel->jsonInsert($uid,$source,$file,$id,'meta_version',$meta[$prefix]['meta_version']);
                                array_push($done['meta'], $prefix);
                            }
                        } 
                    }	
                }
            }
        }
        // This check is here for when we travel up at least two array levels
        // This will add the negated 0 to those cases
        if (!$done['negated']['true'] && !empty($done['negated']['uid'])) {
            $done['negated']['true'] = 1;
            $uploadModel->jsonInsert($done['negated']['uid'],$source,$file,$id,'negated',0);	
        }
        // Pass the current uid in use to the negated check to the array above so
        // we know which group to add negated 0 to (if applicable)
        $done['negated']['uid'] = $uid;
        return $done;
    }

    /**
     * Cell Base Api - Function to handle the top end of api calls to cell base
     *
     * @param array $compact - List of ten variables to pass into this function
     * ("assembly","chrom","coordinateSystem","pos","ref","alt","uid","id","file","source");
     * @return array $result - Returns result from the api call    
     */
    public function cellBaseApi($compact) {
        $uploadModel = new Upload($this->db);

        extract($compact);
        // error_log("cell_base");
        // $arr = get_defined_vars();
        // error_log(print_r($arr,1));
        if(empty($chrom) && empty($pos)) {

        }
        if ($coordinateSystem == "ONE_BASED" || $coordinateSystem == 1) {

        }
        elseif ($coordinateSystem == "ZERO_BASED" || $coordinateSystem == 0 || $coordinateSystem == "") {
            $pos++;
        }
        if ($assembly == "GRCH_37") {
            $assembly = "GRCh37";
        }
        elseif ($assembly == "GRCH_38") {
            $assembly = "GRCh38";
        }
        $posLength = strlen($ref);
        $posEnd = $pos + $posLength -1;
        $possibleRef = $ref;

        if (!is_null($this->variantValidatorCurl($chrom,$pos,$assembly,$possibleRef,$alt))) {
            error_log("failed petes api");
            $message = "Variant is not valid, as according to VariantValidator. https://variantvalidator.org/";
            $error_code = 3;
            $uploadModel->errorInsert($file,$source,$message,$error_code,true,true);
            return;
        }
        else {
            error_log("passed");
        }
        $results = json_decode($this->variantAnnotationCurl($chrom,$pos,$assembly,$possibleRef,$alt),1);
        $results = $results['response'][0]['result'][0]['consequenceTypes'];
        $output = [];
        // $counter = 0;
        for ($i=0; $i <count($results) ; $i++) { 
            if (!isset( $results[$i]['geneName'])) {
                continue;
            }
            $geneName = $results[$i]['geneName'];
            if (!array_key_exists($geneName, $output)) {
                $output[$geneName] = [];
                $output[$geneName]['geneName'] = $geneName;
                $output[$geneName]['ensemblGeneId'] = $results[$i]['ensemblGeneId'];
            }
            $string = "";
            $first = true;
            for ($t=0; $t < count($results[$i]['sequenceOntologyTerms']) ; $t++) { 
                if (!array_key_exists($results[$i]['sequenceOntologyTerms'][$t]['name'], $output[$geneName])) {
                    if ($first) {
                        $output[$geneName][$results[$i]['sequenceOntologyTerms'][$t]['name']] = [];
                        $temp = &$output[$geneName][$results[$i]['sequenceOntologyTerms'][$t]['name']];
                        $temp['sequenceOntologyTerms'] = [];
                        $first = false;
                    }					
                    $temp['sequenceOntologyTerms'][]['name'] = $results[$i]['sequenceOntologyTerms'][$t]['name'];
                    $temp['sequenceOntologyTerms'][]['accession'] = $results[$i]['sequenceOntologyTerms'][$t]['accession'];
                }
            }	
        }
        // error_log(print_r($output,1));
        foreach ($output as $key => $value) {
            $uid = md5(uniqid(rand(),true));
            $uploadModel->jsonInsert($uid,$source,$file,$id,"genomeAssembly",$assembly);  
            $uploadModel->jsonInsert($uid,$source,$file,$id,"sequence",$chrom);  
            $uploadModel->jsonInsert($uid,$source,$file,$id,"coordinateSystem",$coordinateSystem);  
            $uploadModel->jsonInsert($uid,$source,$file,$id,"position",$pos);  
            $uploadModel->jsonInsert($uid,$source,$file,$id,"deletion",$ref);  
            $uploadModel->jsonInsert($uid,$source,$file,$id,"insertion",$alt);  
            $uploadModel->recursiveCell($output[$key],$uid,$source,$file,$id);
        }
        return $pos;
        // error_log(print_r($output,1));
    }

    /**
     * Variant Validator Curl - Hit Variant Validator to confirm given variant details are
     * accurate
     * 
     * @param int $chrom         - Chromosome we are checking
     * @param int $position      - Start ref position
     * @param string $assembly   - Which Genome build we are checking
     * @param string $ref        - The Reference Base
     * @param string $alt        - The Alternate Base
     * @return null $result if variant is correct | string $result if incorrect
     */
    function variantValidatorCurl($chrom,$position,$assembly,$ref,$alt) {
        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init(); 

        // Temporarily disable ssl verifier to avoid the ambiguous self signed ssl error 60
        // by Mehdi Mehtarizadeh
        // 05/08/2019
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        curl_setopt($ch, CURLOPT_URL, 'https://rest.variantvalidator.org/variantformatter/'.$assembly.'/'.$chrom.'-'.$position.'-'.$ref.'-'.$alt.'/None/None/True'); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $result = json_decode(curl_exec($ch),1); 
        // error_log(print_r($result,1));
        if (curl_errno($ch)) { 
            echo 'Error:' . curl_error($ch); 
        } 
        curl_close ($ch);
        $target = $chrom."-".$position."-".$ref."-".$alt;
        $result = $result[$target][$target]['genomic_variant_error'];
        return $result;
    }

    /**
     * Variant Annotation Curl - Hit CellBase API post /genomic/variant/annotation endpoint
     * to get variant data 
     * 
     * @param int $chrom         - Chromosome we are checking
     * @param int $positionStart - Start ref position
     * @param string $assembly   - Which Genome build we are checking
     * @param string $ref        - The Reference Base
     * @param string $alt        - The Alternate Base
     * @return array $result     - Returns result from the api call
     */
    function variantAnnotationCurl($chrom,$positionStart,$assembly,$ref,$alt) {
        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();
        
        // Temporarily disable ssl verifier to avoid the ambiguous self signed ssl error 60
        // by Mehdi Mehtarizadeh
        // 05/08/2019
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        curl_setopt($ch, CURLOPT_URL, 'http://bioinfo.hpc.cam.ac.uk/cellbase/webservices/rest/v4/hsapiens/genomic/variant/annotation?assembly='.$assembly.'&limit=-1&skip=-1&skipCount=false&count=false&Output%20format=json&normalize=false&phased=false&useCache=false&imprecise=true&svExtraPadding=0&cnvExtraPadding=0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $chrom.":".$positionStart.":".$ref.":".$alt);
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Content-Type: text/plain';
        $headers[] = 'Accept: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);
        return $result;
    }

    /**
     * Recursive Cell - Recursive Loop to append data into eavs of all data from all CellBase Data
     *
     * @param array $array - Array we adding from
     * @param string $uid  - The group id we are adding to
     * @param int $source  - The source we are adding to
     * @param int $file    - The file Id we are adding to
     * @param int $id      - The subject Id we are adding to
     * @return N/a
     */
    function recursiveCell($array,$uid,$source,$file,$id) {
        foreach($array as $key => $value){
            if(is_array($value)){
                $output =$this->recursiveCell($value,$uid,$source,$file,$id);
            }
            else {
                $uploadModel->jsonInsert($uid,$source,$file,$id,$key,$value);  
            }
        }
    }

    /**
     * Send Batch - Fill in the status table on the success of the upload
     *
     * @param N/A
     * @return boolean $error - True if the transaction failed 
     */
    public function sendBatch() {
        $dbRet = $this->db->transComplete();
        // select has had some problem.
        if ($this->db->transStatus() === FALSE) {
            $error = true;
            return $error;
        }
        else {
            $this->db->transStart();
        }
    }

    public  function set_config($key, $value, $json) {
        $levels = array();
    
        if (FALSE===(preg_match('/:/', $key))) {
            $levels[0] = $key;
        }
        else {
            $levels=explode(':',$key);
        }
        // error_log(print_r($levels,1));
        $pointer =& $json;
        for ($i=0; $i<count($levels); $i++) {
            if (isset($pointer[$levels[$i]])) {
                $pointer =& $pointer[$levels[$i]];
            }
            else {
                $pointer[$levels[$i]]=array();
                $pointer =& $pointer[$levels[$i]];
            }
          } 
        array_push($pointer, $value);
    
        // error_log(print_r($json,1));
        return $json;
    }

    /**
     * Mempty - Checks all parameters (variable amount) 
     * If any are empty return false. If all have content return true.
     *
     * @param string - Variable amount of parameters
     * @return false if any params are empty | true if none are empty
     */
    public function mempty() {
        foreach(func_get_args() as $arg) {
            if(empty($arg)) {		        	
                return false;
            }
        }     
        return true;
    }

 }