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

    public function absorb(int $file_id): bool
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

                if($this->delete == UPLOADER_DELETE_FILE){
                    $this->reportProgress($file_id, 0, 1, 'bulkupload', 'Deleting existing data for the file');
					$this->deleteExistingRecords($file_id);
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

    public function save(int $file_id): bool
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
        $hpo_terms = array_unique($this->arrSearch($this->data, $output));
		if (count($hpo_terms) > 0) {
			$hpo_attribute_id = $this->getAttributeIdByName($this->configuration['hpo_attribute_name']);
			$hpo_value_ids = [];
			for ($i = 0; $i < count($hpo_terms); $i++) {
				$hpo_value_id = $this->getValueIdByNameAndAttributeId($hpo_terms[$i], $this->configuration['hpo_attribute_name']);
				array_push($hpo_value_ids, $hpo_value_id);
			}

			$matches = $this->eavModel->checkNegatedForHPO($hpo_value_ids, $hpo_attribute_id);
			for ($i = 0; $i < count($matches); $i++) {
				$hpo_term = $this->getValueByValueIdAndAttributeName($matches[$i], $this->configuration['hpo_attribute_name']);
				$result = $neo4jInterface->GetAncestors($hpo_term);
				if (count($result) > 0) {
					foreach ($result as $key => $value) {
						if (!array_key_exists($key, $hits)) {
							$hits[$key] = $value;
						}
					}
				}
			}
		}

        $uid = md5(uniqid(rand(),true));

		$type_attribute_id = $this->getAttributeIdByName('type'); // Add an attribute by the name 'type' and get its id
		$ancestor_value_id = $this->getValueIdByNameAndAttributeId('ancestor', 'type'); // Add a value for type called ancestor
        $this->createEAV($uid, $file_id, $this->id, $type_attribute_id, $ancestor_value_id); // Create EAV relationship between 'type' and 'ancestor'

		$ancestor_hpo_attribute_id = $this->getAttributeIdByName('ancestor_hpo_id'); // Add an attribute by the name 'ancestor_hpo_id' and get its id
		$ancestor_hpo_label_attribute_id = $this->getAttributeIdByName('ancestor_hpo_label'); // Add an attribute by the name 'ancestor_hpo_label'

        foreach ($hits as $key => $value) {
			$key_value_id = $this->getValueIdByNameAndAttributeId($key, 'ancestor_hpo_id'); // Add a value for 'ancestor_hpo_id'
			$this->createEAV($uid, $file_id, $this->id, $ancestor_hpo_attribute_id, $key_value_id); // Create EAV relationship between 'ancestor_hpo_id' and $key_value_id

			$value_id = $this->getValueIdByNameAndAttributeId($value, 'ancestor_hpo_label'); // Add a value for 'ancestor_hpo_label'
            $this->createEAV($uid, $file_id, $this->id, $ancestor_hpo_label_attribute_id, $value_id); // Create EAV relationship between 'ancestor_hpo_label' and $value_id
        }

        $dbRet = $this->db->commit();

        if ($dbRet === false) {
            $message = "Data Failed to insert. Please double check file for sanity.";
            $error_code = 4;
            $this->uploadModel->errorInsert($file_id, $this->sourceId, $message, $error_code, true);
        }
        else {
            $this->reportProgress($file_id, 3, $steps, 'bulkupload');
            $this->reportProgress($file_id, 1, 1, 'bulkupload', 'Finished', true);
        }

		//Update value frequencies
		$this->updateValueFrequencies();

		//Set attributes types, minimum, and maximum values if applicable
		$this->determineAttributesType();
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
						$type_attribute_id = $this->getAttributeIdByName('type'); // Get attribute id of 'type'
						$type_value_id = $this->getValueIdByNameAndAttributeId($type, 'type'); // Add $type as a value for 'type' and get its id
                        $this->createEAV($uid, $file, $id, $type_attribute_id, $type_value_id); // Add the EAV relationship
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
						$type_attribute_id = $this->getAttributeIdByName('type'); // Get attribute id of 'type'
						$type_value_id = $this->getValueIdByNameAndAttributeId($type, 'type'); // Add $type as a value for 'type' and get its id
                        $this->createEAV($uid, $file, $id, $type_attribute_id, $type_value_id);
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

				$string_attribute_id = $this->getAttributeIdByName($string); // Get attribute id of '$string'
				$value_id = $this->getValueIdByNameAndAttributeId($value, $string); // Add '$value' as a value for '$string' and get its id
                $this->createEAV($uid, $file, $id, $string_attribute_id, $value_id);

                // Since we have just an id row we need to check its id group with our list of meta
                // attributes (if applicable)
                if ($key == "id") {
                    $prefix = explode(":", $value);
                    $prefix = $prefix[0];
                    if ($meta) {
                        if (array_key_exists($prefix, $meta)) {
                            // Since it exists now check if we have already added these meta rows before to this group
                            if (!in_array($prefix, $done['meta'])) {
								$meta_name_attribute_id = $this->getAttributeIdByName('meta_name'); // Get attribute id of 'meta_name'
								$meta_name_value_id = $this->getValueIdByNameAndAttributeId($meta[$prefix]['meta_name'], 'meta_name'); // Add '$meta[$prefix]['meta_name']' as a value for 'meta_name' and get its id
                                $this->createEAV($uid, $file, $id,$meta_name_attribute_id, $meta_name_value_id);

								$meta_version_attribute_id = $this->getAttributeIdByName('meta_version'); // Get attribute id of 'meta_version'
								$meta_version_value_id = $this->getValueIdByNameAndAttributeId($meta[$prefix]['meta_version'], 'meta_version'); // Add '$meta[$prefix]['meta_version']' as a value for 'meta_version' and get its id
								$this->createEAV($uid, $file, $id,$meta_version_attribute_id, $meta_version_value_id);

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
    private function arrSearch(array $array, array $output): array
	{
        foreach($array as $key => $value){
            if(is_array($value)){
                $output = $this->arrSearch($value, $output);
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
							$type_attribute_id = $this->getAttributeIdByName('type'); // Get attribute id of 'type'
							$type_value_id = $this->getValueIdByNameAndAttributeId($type, 'type'); // Add $type as a value for 'type' and get its id
                            $this->createEAV($uid, $file, $id, $type_attribute_id, $type_value_id);
                            array_push($done['type'], $type);
                        }
                }
                else {
                    if ($key == "metaData") {
                        continue;
                    }
                    if ($key != "type") {
                        $type = $key;
                        if ($uid) {
                            if (!in_array($type, $done['type'])) {
								$type_attribute_id = $this->getAttributeIdByName('type'); // Get attribute id of 'type'
								$type_value_id = $this->getValueIdByNameAndAttributeId($type, 'type'); // Add $type as a value for 'type' and get its id
                                $this->createEAV($uid, $file, $id, $type_attribute_id, $type_value_id);
                                array_push($done['type'], $type);
                            }
                        }

                    }
                }
				if ($type == $this->configuration['hpo_attribute_name'] && $key == 'type'){
					if (array_key_exists('negated',$array)){
						if ($array['negated'] == true){
							$negated_hpo_attribute_id = $this->getAttributeIdByName($this->configuration['negated_hpo_attribute_name']); // Get attribute id of '$this->configuration['negated_hpo_attribute_name']'
							$value_id = $this->getValueIdByNameAndAttributeId($value['id'], $this->configuration['negated_hpo_attribute_name']); // Add $value['id'] as a value for '$this->configuration['negated_hpo_attribute_name']' and get its id
							$this->createEAV($uid, $file, $id, $negated_hpo_attribute_id, $value_id);
						}
						else{
							$hpo_attribute_id = $this->getAttributeIdByName($this->configuration['hpo_attribute_name']); // Get attribute id of '$this->configuration['hpo_attribute_name']
							$value_id = $this->getValueIdByNameAndAttributeId($value['id'], $this->configuration['hpo_attribute_name']); // Add $value['id'] as a value for '$this->configuration['hpo_attribute_name']' and get its id
							$this->createEAV($uid, $file, $id, $hpo_attribute_id, $value_id);
						}
					}
					else{
						$hpo_attribute_id = $this->getAttributeIdByName($this->configuration['hpo_attribute_name']); // Get attribute id of '$this->configuration['hpo_attribute_name']
						$value_id = $this->getValueIdByNameAndAttributeId($value['id'], $this->configuration['hpo_attribute_name']); // Add $value['id'] as a value for '$this->configuration['hpo_attribute_name']' and get its id
						$this->createEAV($uid, $file, $id, $hpo_attribute_id, $value_id);
					}

					if (!in_array($type, $done['type'])) {
						$type_attribute_id = $this->getAttributeIdByName('type'); // Get attribute id of 'type'
						$type_value_id = $this->getValueIdByNameAndAttributeId($type, 'type'); // Add $type as a value for 'type' and get its id
						$this->createEAV($uid, $file, $id, $type_attribute_id, $type_value_id);
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

				$string_attribute_id = $this->getAttributeIdByName($string); // Get attribute id of '$string'
				$value_id = $this->getValueIdByNameAndAttributeId($type, $string); // Add $value as a value for '$string' and get its id
                $this->createEAV($uid, $file, $id, $string_attribute_id, $value_id);
                // Since we have just an id row we need to check its id group with our list of meta
                // attributes (if applicable)
                if ($key == "id") {
                    $prefix = explode(":", $value);
                    $prefix = $prefix[0];
                    if ($meta) {
                        if (array_key_exists($prefix, $meta)) {
                            // Since it exists now check if we have already added these meta rows before to this group
                            if (!in_array($prefix, $done['meta'])) {
								$meta_name_attribute_id = $this->getAttributeIdByName('meta_name'); // Get attribute id of 'meta_name'
								$meta_name_value_id = $this->getValueIdByNameAndAttributeId($meta[$prefix]['meta_name'], 'meta_name'); // Add '$meta[$prefix]['meta_name']' as a value for 'meta_name' and get its id
								$this->createEAV($uid, $file, $id, $meta_name_attribute_id, $meta_name_value_id);

								$meta_version_attribute_id = $this->getAttributeIdByName('meta_version'); // Get attribute id of 'meta_version'
								$meta_version_value_id = $this->getValueIdByNameAndAttributeId($meta[$prefix]['meta_version'], 'meta_version'); // Add '$meta[$prefix]['meta_version']' as a value for 'meta_version' and get its id
								$this->createEAV($uid, $file, $id, $meta_version_attribute_id, $meta_version_value_id);

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
			$genome_assembly_attribute_id = $this->getAttributeIdByName('genomeAssembly'); // Get attribute id of 'genomeAssembly'
			$sequence_attribute_id = $this->getAttributeIdByName('sequence'); // Get attribute id of 'sequence'
			$coordinate_system_attribute_id = $this->getAttributeIdByName('coordinateSystem'); // Get attribute id of 'coordinateSystem'
			$position_attribute_id = $this->getAttributeIdByName('position'); // Get attribute id of 'position'
			$deletion_attribute_id = $this->getAttributeIdByName('deletion'); // Get attribute id of 'deletion'
			$insertion_attribute_id = $this->getAttributeIdByName('insertion'); // Get attribute id of 'insertion'

			$assembly_value_id = $this->getValueIdByNameAndAttributeId($assembly, 'genomeAssembly'); // Add '$assembly' as a value for 'genomeAssembly' and get its id
			$chrom_value_id = $this->getValueIdByNameAndAttributeId($chrom, 'sequence'); // Add '$chrom' as a value for 'sequence' and get its id
			$coordinate_ystem_value_id = $this->getValueIdByNameAndAttributeId($coordinateSystem, 'coordinateSystem'); // Add '$coordinateSystem' as a value for 'coordinateSystem' and get its id
			$pos_value_id = $this->getValueIdByNameAndAttributeId($pos, 'position'); // Add '$pos' as a value for 'position' and get its id
			$ref_value_id = $this->getValueIdByNameAndAttributeId($ref, 'deletion'); // Add '$ref' as a value for 'deletion' and get its id
			$alt_value_id = $this->getValueIdByNameAndAttributeId($alt, 'insertion'); // Add '$alt' as a value for 'insertion' and get its id

			$this->createEAV($uid, $file, $id, $genome_assembly_attribute_id, $assembly_value_id);
            $this->createEAV($uid, $file, $id, $sequence_attribute_id, $chrom_value_id);
            $this->createEAV($uid, $file, $id, $coordinate_system_attribute_id, $coordinate_ystem_value_id);
            $this->createEAV($uid, $file, $id, $position_attribute_id, $pos_value_id);
            $this->createEAV($uid, $file, $id, $deletion_attribute_id, $ref_value_id);
            $this->createEAV($uid, $file, $id, $insertion_attribute_id, $alt_value_id);
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
                $output = $this->recursiveCell($value,$uid,$source,$file,$id);
            }
            else {
				$key_attribute_id = $this->getAttributeIdByName($key); // Get attribute id of '$key'
				$value_id = $this->getValueIdByNameAndAttributeId($value, $key); // Add '$value' as a value for '$key' and get its id
				$this->createEAV($uid, $file, $id, $key_attribute_id, $value_id);
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
