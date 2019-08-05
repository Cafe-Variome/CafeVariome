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

 class Task extends Controller{

    function __construct(){
        $this->db = \Config\Database::connect();

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
            $data = json_decode(file_get_contents(FCPATH . "upload/UploadData/$source_id/json/$file"), true);
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
                $this->errorInsert($file,$source,$message,$error_code,true);
            }
            // update status table to class current file as inserted to database
            $uploadModel->insertStatistics($file_id, $source_id);
            $uploadModel->bigInsertWrap($file_id, $source_id);
        }
        // we have finished updating the source and unlock it so further uploads and updates can be performed
        $sourceModel->toggleSourceLock($source_id);	
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
        // error_log($possibleRef);
        // $possibleRef = json_decode($this->sequenceCurl($chrom,$pos,$posEnd,$assembly),1);

        // if (!empty($possibleRef['response'][0]['result'])) {
        // 	$possibleRef = $possibleRef['response'][0]['result'][0]['sequence'];
        // 	error_log($possibleRef);
        // }
        // error_log(print_r($possibleRef,1));
        // error_log($possibleRef);
        if (!is_null($this->variantValidatorCurl($chrom,$pos,$assembly,$possibleRef,$alt))) {
            error_log("failed petes api");
            $message = "Variant is not valid, as according to VariantValidator. https://variantvalidator.org/";
            $error_code = 3;
            $this->errorInsert($file,$source,$message,$error_code,true,true);
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