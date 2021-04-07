<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Input;

/**
 * Name PhenoPacketDataInput.php
 * 
 * Created 22/03/2021
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 * 
 */

use App\Libraries\CafeVariome\Core\IO\FileSystem\SysFileMan;
use App\Libraries\CafeVariome\Net\CellBaseNetworkInterface;
use App\Libraries\CafeVariome\Net\VariantValidatorNetworkInterface;
use App\Libraries\CafeVariome\Net\HPONetworkInterface;
use App\Libraries\CafeVariome\Net\ServiceInterface;

class PhenoPacketDataInput extends DataInput
{
    private $data;
    private $meta;
    private $id;

    private $overwrite;
    private $serviceInterface;

    public function __construct(int $source_id, bool $overwrite)
    {
        parent::__construct($source_id);
        $this->overwrite = $overwrite;
        $this->serviceInterface = new ServiceInterface();
    }

    public function absorb(int $file_id)
    {
        $this->serviceInterface->RegisterProcess($file_id, 1, 'bulkupload', "Starting");

        $fileRecord = $this->getSourceFiles($file_id);

        if (count($fileRecord) == 1) {
            $file = $fileRecord[0]['FileName'];

            if ($this->fileMan->Exists($file)) {
                $fileContent = $this->fileMan->Read($file);
                $this->data = json_decode($fileContent, true);
    
                if ($this->overwrite) {
                    $this->serviceInterface->ReportProgress($file_id, 0, 1, 'bulkupload', 'Deleting existing data');

                    $this->uploadModel->phenoPacketClear($this->sourceId, $file_id);
                    $this->uploadModel->clearErrorForFile($file_id);
                }

                $this->meta = null;
    
                if (array_key_exists("metaData", $this->data)) {
                    $this->meta = [];
                    $target = &$this->data['metaData']['resources'];
    
                    for ($i=0; $i < count($target); $i++) { 
                        $this->meta[$target[$i]['namespacePrefix']] = [];
                        $this->meta[$target[$i]['namespacePrefix']]['meta_name'] = $target[$i]['name'];
                        $this->meta[$target[$i]['namespacePrefix']]['meta_version'] = $target[$i]['version'];
                    }		
                }
    
                preg_match("/(.*)\./", $file, $matches);
                $this->data['id'] = $matches[1];
                $this->id = $this->data['id'];
            }
        }
    }

    public function save(int $file_id)
    {
        $steps = 3;
        $HPOApi = new HPONetworkInterface();

        $this->sourceModel->lockSource($this->sourceId);

        $this->db->transStart();	
        $this->serviceInterface->ReportProgress($file_id, 0, $steps, 'bulkupload', 'Importing data');
		
        // perform recursive insert for given file
        $done = $this->recursivePacket($this->data, $this->meta, $this->id, $file_id, $this->sourceId, null, null, null, null);

        $this->serviceInterface->ReportProgress($file_id, 1, $steps, 'bulkupload');

        if (!$done['negated']['true'] && !empty($done['negated']['uid'])) {
            $this->uploadModel->jsonInsert($done['negated']['uid'], $this->sourceId, $file_id, $this->id, 'negated', 0);	
        }

        $this->serviceInterface->ReportProgress($file_id, 2, $steps, 'bulkupload');


        $output = [];
        $hits = [];
        $matches = array_unique($this->arrSearch($this->data, $output));
        $matches = $this->uploadModel->checkNegatedForHPO($matches, $file_id);
        for ($i=0; $i < count($matches); $i++) { 
            $result = $HPOApi->getAncestor($matches[$i]);
            if (property_exists($result, 'ancestor')) {
                foreach ($result->ancestor as $key => $value) {
                    if (!array_key_exists($value->id, $hits)) {
                        $hits[$value->id] = $value->label;
                    }
                }
            }						
        }

        $uid = md5(uniqid(rand(),true));	
        $this->uploadModel->jsonInsert($uid, $this->sourceId, $file_id, $this->id, 'type', 'ancestor');

        foreach ($hits as $key => $value) {
            $this->uploadModel->jsonInsert($uid, $this->sourceId, $file_id, $this->id, 'ancestor_hpo_id', $key);
            $this->uploadModel->jsonInsert($uid, $this->sourceId, $file_id, $this->id, 'ancestor_hpo_label', $value);
        }

        $dbRet = $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            $error = true;
            error_log($this->db->transStatus());
            $message = "Data Failed to insert. Please double check file for sanity.";
            $error_code = 4;
            $this->uploadModel->errorInsert($file, $this->sourceId, $message, $error_code, true);
        }
        else {
            $this->serviceInterface->ReportProgress($file_id, 3, $steps, 'bulkupload');
            $this->serviceInterface->ReportProgress($file_id, 1, 1, 'bulkupload', 'Finished', true);
        }

        $totalRecordCount = $this->sourceModel->countSourceEntries($this->sourceId);

        $this->sourceModel->updateSource(['record_count' => $totalRecordCount], ['source_id' => $this->sourceId]);

        // update status table to class current file as inserted to database
        $this->uploadModel->insertStatistics($file_id, $this->sourceId);
        $this->uploadModel->bigInsertWrap($file_id, $this->sourceId);

        $this->sourceModel->unlockSource($this->sourceId);

    }

    /**
     * Recursive Packet - Up to Date Recursive Loop to iterate through a multi nested 
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
    private function recursivePacket($array,$meta,$id,$file,$source,$uid,$type,$one_group,$done) {

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
                        $this->uploadModel->jsonInsert($done['negated']['uid'],$source,$file,$id,'negated',0);	
                    }
                    $uid = md5(uniqid(rand(),true));
                    // Since this is an array key all sub arrays remain part of the same group
                    // One group is set to true to ensure this
                    $one_group = true;
                    
                    // reset done array to keep track of whether negated has been added and which meta rows have been added
                    $done = ['meta' => [],'negated' => ['true' => 0],'cell' => [], "type" => []];
                    if (!in_array($type, $done['type'])) {
                        $this->uploadModel->jsonInsert($uid,$source,$file,$id,'type',$type);
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
                    if (!empty($done['negated']['uid']) && !$done['negated']['true']) {
                        $this->uploadModel->jsonInsert($done['negated']['uid'],$source,$file,$id,'negated',0);	
                    }
                    $uid = md5(uniqid(rand(),true));
                    $one_group = true;
                    $done = ['meta' => [],'negated' => ['true' => 0],'cell' => [], "type" => []];
                    if (!in_array($type, $done['type'])) {
                        $this->uploadModel->jsonInsert($uid,$source,$file,$id,'type',$type);
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
                $this->uploadModel->jsonInsert($uid,$source,$file,$id,$string,$value);  
                // Since we have just an id row we need to check its id group with our list of meta
                // attributes (if applicable)
                if ($key == "id") {
                    $prefix = explode(":", $value);
                    $prefix = $prefix[0];
                    if ($meta) {
                        if (array_key_exists($prefix, $meta)) {
                            // Since it exists now check if we have already added these meta rows before to this group
                            if (!in_array($prefix, $done['meta'])) {
                                $this->uploadModel->jsonInsert($uid,$source,$file,$id,'meta_name',$meta[$prefix]['meta_name']);
                                $this->uploadModel->jsonInsert($uid,$source,$file,$id,'meta_version',$meta[$prefix]['meta_version']);
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
            $this->uploadModel->jsonInsert($done['negated']['uid'],$source,$file,$id,'negated',0);	
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
    private function arrSearch($array,$output){
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
    private function recursiveNumeric($array,$meta,$id,$file,$source,$uid,$type,$one_group,$done) {
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
                            $this->uploadModel->jsonInsert($uid,$source,$file,$id,'type',$type);
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
                                $this->uploadModel->jsonInsert($uid,$source,$file,$id,'type',$type);
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
                $this->uploadModel->jsonInsert($uid,$source,$file,$id,$string,$value);  
                // Since we have just an id row we need to check its id group with our list of meta
                // attributes (if applicable)
                if ($key == "id") {
                    $prefix = explode(":", $value);
                    $prefix = $prefix[0];
                    if ($meta) {
                        if (array_key_exists($prefix, $meta)) {
                            // Since it exists now check if we have already added these meta rows before to this group
                            if (!in_array($prefix, $done['meta'])) {
                                $this->uploadModel->jsonInsert($uid,$source,$file,$id,'meta_name',$meta[$prefix]['meta_name']);
                                $this->uploadModel->jsonInsert($uid,$source,$file,$id,'meta_version',$meta[$prefix]['meta_version']);
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
            $this->uploadModel->jsonInsert($done['negated']['uid'],$source,$file,$id,'negated',0);	
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
    private function cellBaseApi($compact) {

        $cellBaseApi = new CellBaseNetworkInterface();
        $variantValidatorApi = new VariantValidatorNetworkInterface();

        extract($compact);

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

        
        if (!is_null($variantValidatorApi->ValidateVariant($chrom, $pos, $ref, $alt, $assembly))) {
            $message = "Variant is not valid, as according to VariantValidator. https://variantvalidator.org/";
            $error_code = 3;
            $this->uploadModel->errorInsert($file,$source,$message,$error_code,true,true);
            return;
        }

        $results = $cellBaseApi->AnnotateVariant($chrom, $pos, $ref, $alt, $assembly);
        $results = $results->response[0]->result[0]->consequenceTypes;
        $output = [];
        // $counter = 0;
        for ($i=0; $i <count($results) ; $i++) { 
            if (!isset( $results[$i]->geneName)) {
                continue;
            }
            $geneName = $results[$i]->geneName;
            if (!array_key_exists($geneName, $output)) {
                $output[$geneName] = [];
                $output[$geneName]['geneName'] = $geneName;
                $output[$geneName]['ensemblGeneId'] = $results[$i]->ensemblGeneId;
            }
            $string = "";
            $first = true;
            for ($t=0; $t < count($results[$i]->sequenceOntologyTerms) ; $t++) { 
                if (!array_key_exists($results[$i]->sequenceOntologyTerms[$t]->name, $output[$geneName])) {
                    if ($first) {
                        $output[$geneName][$results[$i]->sequenceOntologyTerms[$t]->name] = [];
                        $temp = &$output[$geneName][$results[$i]->sequenceOntologyTerms[$t]->name];
                        $temp['sequenceOntologyTerms'] = [];
                        $first = false;
                    }					
                    $temp['sequenceOntologyTerms'][]['name'] = $results[$i]->sequenceOntologyTerms[$t]->name;
                    $temp['sequenceOntologyTerms'][]['accession'] = $results[$i]->sequenceOntologyTerms[$t]->accession;
                }
            }	
        }

        foreach ($output as $key => $value) {
            $uid = md5(uniqid(rand(),true));
            $this->uploadModel->jsonInsert($uid,$source,$file,$id,"genomeAssembly",$assembly);  
            $this->uploadModel->jsonInsert($uid,$source,$file,$id,"sequence",$chrom);  
            $this->uploadModel->jsonInsert($uid,$source,$file,$id,"coordinateSystem",$coordinateSystem);  
            $this->uploadModel->jsonInsert($uid,$source,$file,$id,"position",$pos);  
            $this->uploadModel->jsonInsert($uid,$source,$file,$id,"deletion",$ref);  
            $this->uploadModel->jsonInsert($uid,$source,$file,$id,"insertion",$alt);  
            $this->recursiveCell($output[$key],$uid,$source,$file,$id);
        }
        return $pos;
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
    private function recursiveCell($array,$uid,$source,$file,$id) {

        foreach($array as $key => $value){
            if(is_array($value)){
                $output =$this->recursiveCell($value,$uid,$source,$file,$id);
            }
            else {
                $this->uploadModel->jsonInsert($uid,$source,$file,$id,$key,$value);  
            }
        }
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
