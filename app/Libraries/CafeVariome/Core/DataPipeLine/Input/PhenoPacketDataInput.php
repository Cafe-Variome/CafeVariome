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
use App\Libraries\CafeVariome\Core\DataPipeLine\Stream\Neo4J;

class PhenoPacketDataInput extends DataInput
{
    private $data;
    private $meta;
    private $id;
    protected $configuration;
    private $delete;

    public function __construct(int $source_id, int $delete)
    {
        parent::__construct($source_id);
        $this->delete = $delete;
        $this->initializeConfiguration();
    }

    public function absorb(int $file_id)
    {
		$this->registerProcess($file_id);

        $fileRecord = $this->getSourceFiles($file_id);

        if (count($fileRecord) == 1) {
            $file = $fileRecord[0]['FileName'];
			$this->fileName = $file;
			if (array_key_exists('pipeline_id', $fileRecord[0])) {
                $this->pipeline_id = $fileRecord[0]['pipeline_id'];
                $this->applyPipeline($this->pipeline_id);
            }

            if ($this->fileMan->Exists($file)) {
                $fileContent = $this->fileMan->Read($file);
                $this->data = json_decode($fileContent, true);

                if ($this->delete == UPLOADER_DELETE_ALL) {
                    $this->reportProgress($file_id, 0, 1, 'bulkupload', 'Deleting existing data for the source');
                    $this->eavModel->deleteRecordsBySourceId($this->sourceId);
                    $this->delete = true;
                }
                else if($this->delete == UPLOADER_DELETE_FILE){
                    $this->reportProgress($file_id, 0, 1, 'bulkupload', 'Deleting existing data for the file');
                    $this->eavModel->deleteRecordsByFileId($file_id);
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

                if($this->configuration['subject_id_location'] == SUBJECT_ID_WITHIN_FILE){
                    $this->id = $this->data['subject']['id'];
                }
                else if($this->configuration['subject_id_location'] == SUBJECT_ID_IN_FILE_NAME){
                    preg_match("/(.*)\./", $file, $matches);
                    $this->id = $matches[1];
                }
            }
        }
    }

    public function save(int $file_id)
    {
        $steps = 3;
        $neo4jInterface = new Neo4J();

        $this->sourceModel->lockSource($this->sourceId);

        $this->db->begin_transaction();
        $this->reportProgress($file_id, 0, $steps, 'bulkupload', 'Importing data');

		$done = ['meta' => [],'negated' => ['true' => 0],'cell' => [], "type" => []];
        // perform recursive insert for given file
        $done = $this->recursivePacket($this->data, $this->meta, $this->id, $file_id, $this->sourceId, null, null, null, $done);

        $this->reportProgress($file_id, 1, $steps, 'bulkupload');

        $this->reportProgress($file_id, 2, $steps, 'bulkupload');

        $output = [];
        $hits = [];
        $matches = array_unique($this->arrSearch($this->data, $output));
        $matches = $this->eavModel->checkNegatedForHPO($matches, $file_id, $this->configuration['hpo_attribute_name']);
        for ($i=0; $i < count($matches); $i++) {
            $result = $neo4jInterface->GetAncestors($matches[$i]);
            if (count($result) > 0) {
                foreach ($result as $key => $value) {
                    if (!array_key_exists($key, $hits)) {
                        $hits[$key] = $value;
                    }
                }
            }
        }

        $uid = md5(uniqid(rand(),true));
        $this->createEAV($uid, $this->sourceId, $file_id, $this->id, 'type', 'ancestor');

        foreach ($hits as $key => $value) {
            $this->createEAV($uid, $this->sourceId, $file_id, $this->id, 'ancestor_hpo_id', $key);
            $this->createEAV($uid, $this->sourceId, $file_id, $this->id, 'ancestor_hpo_label', $value);
        }

        $dbRet = $this->db->commit();

        if ($dbRet === FALSE) {
            $message = "Data Failed to insert. Please double check file for sanity.";
            $error_code = 4;
            $this->uploadModel->errorInsert($file_id, $this->sourceId, $message, $error_code, true);
        }
        else {
            $this->reportProgress($file_id, 3, $steps, 'bulkupload');
            $this->reportProgress($file_id, 1, 1, 'bulkupload', 'Finished', true);
        }

		if ($this->delete == 1) {
			$this->removeAttribuesAndValuesFiles($this->fileName);
		}

        $this->dumpAttributesAndValues($file_id);
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
    private function recursivePacket($array,$meta,$id,$file,$source,$uid,$type,$one_group,&$done) {

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
                    $uid = md5(uniqid(rand(),true));
                    // Since this is an array key all sub arrays remain part of the same group
                    // One group is set to true to ensure this
                    $one_group = true;

                    // reset done array to keep track of whether negated has been added and which meta rows have been added

                    if (!in_array($type, $done['type'])) {
                        $this->createEAV($uid,$source,$file,$id,'type',$type);
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
                    $uid = md5(uniqid(rand(),true));
                    $one_group = true;
                    //$done = ['meta' => [],'negated' => ['true' => 0],'cell' => [], "type" => []];
                    if (is_array($type) && !in_array($type, $done['type'])) {
                        $this->createEAV($uid,$source,$file,$id,'type',$type);
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
                $this->createEAV($uid,$source,$file,$id,$string,$value);
                // Since we have just an id row we need to check its id group with our list of meta
                // attributes (if applicable)
                if ($key == "id") {
                    $prefix = explode(":", $value);
                    $prefix = $prefix[0];
                    if ($meta) {
                        if (array_key_exists($prefix, $meta)) {
                            // Since it exists now check if we have already added these meta rows before to this group
                            if (!in_array($prefix, $done['meta'])) {
                                $this->createEAV($uid,$source,$file,$id,'meta_name',$meta[$prefix]['meta_name']);
                                $this->createEAV($uid,$source,$file,$id,'meta_version',$meta[$prefix]['meta_version']);
                                array_push($done['meta'], $prefix);
                            }
                        }
                    }
                }
            }
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
    private function recursiveNumeric($array,$meta,$id,$file,$source,$uid,$type,$one_group,&$done) {
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
                            $this->createEAV($uid,$source,$file,$id,'type',$type);
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
                                $this->createEAV($uid,$source,$file,$id,'type',$type);
                                array_push($done['type'], $type);
                            }
                        }

                    }
                }
				if ($type == $this->configuration['hpo_attribute_name'] && $key == 'type'){
					if (array_key_exists('negated',$array)){
						if ($array['negated'] == true){
							$this->createEAV($uid,$source,$file,$id,$this->configuration['negated_hpo_attribute_name'],$value['id']);
						}
						else{
							$this->createEAV($uid,$source,$file,$id,$this->configuration['hpo_attribute_name'],$value['id']);
						}
					}
					else{
						$this->createEAV($uid,$source,$file,$id,$this->configuration['hpo_attribute_name'],$value['id']);
					}

					if (!in_array($type, $done['type'])) {
						$this->createEAV($uid,$source,$file,$id,'type',$type);
						array_push($done['type'], $type);
					}

					return $done;
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
				if ($key == 'negated') {
					return $done;
				}

				$string = $type."_".$key;

                $this->createEAV($uid,$source,$file,$id,$string,$value);
                // Since we have just an id row we need to check its id group with our list of meta
                // attributes (if applicable)
                if ($key == "id") {
                    $prefix = explode(":", $value);
                    $prefix = $prefix[0];
                    if ($meta) {
                        if (array_key_exists($prefix, $meta)) {
                            // Since it exists now check if we have already added these meta rows before to this group
                            if (!in_array($prefix, $done['meta'])) {
                                $this->createEAV($uid,$source,$file,$id,'meta_name',$meta[$prefix]['meta_name']);
                                $this->createEAV($uid,$source,$file,$id,'meta_version',$meta[$prefix]['meta_version']);
                                array_push($done['meta'], $prefix);
                            }
                        }
                    }
                }
            }
        }
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
            $this->createEAV($uid,$source,$file,$id,"genomeAssembly",$assembly);
            $this->createEAV($uid,$source,$file,$id,"sequence",$chrom);
            $this->createEAV($uid,$source,$file,$id,"coordinateSystem",$coordinateSystem);
            $this->createEAV($uid,$source,$file,$id,"position",$pos);
            $this->createEAV($uid,$source,$file,$id,"deletion",$ref);
            $this->createEAV($uid,$source,$file,$id,"insertion",$alt);
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
                $this->createEAV($uid,$source,$file,$id,$key,$value);
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


    protected function initializeConfiguration()
    {
        $this->configuration = ['subject_id_location' => SUBJECT_ID_WITHIN_FILE,
                                'subject_id_attribute_name' => 'id',
								'hpo_attribute_name' => 'phenotype',
								'negated_hpo_attribute_name' => 'negated_hpo'
        ];
    }

    protected function applyPipeline(int $pipeline_id)
    {
        $pipeline = $this->pipelineModel->getPipeline($pipeline_id);

        if ($pipeline != null) {
            $this->configuration['subject_id_location'] = $pipeline['subject_id_location'];

            if ($pipeline['subject_id_location'] == SUBJECT_ID_WITHIN_FILE && $pipeline['subject_id_attribute_name'] != null && $pipeline['subject_id_attribute_name'] != '') {
                $this->configuration['subject_id_attribute_name'] = $pipeline['subject_id_attribute_name'];
            }

            if ($pipeline['hpo_attribute_name'] != null && $pipeline['hpo_attribute_name'] != ''){
				$this->configuration['hpo_attribute_name'] = $pipeline['hpo_attribute_name'];
			}

			if ($pipeline['negated_hpo_attribute_name'] != null && $pipeline['negated_hpo_attribute_name'] != ''){
				$this->configuration['negated_hpo_attribute_name'] = $pipeline['negated_hpo_attribute_name'];
			}
        }
    }
}
