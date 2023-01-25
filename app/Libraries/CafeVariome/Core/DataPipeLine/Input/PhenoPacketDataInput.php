<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Input;

/**
 * Name PhenoPacketDataInput.php
 *
 * Created 22/03/2021
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 *
 */

use App\Libraries\CafeVariome\Entities\Task;
use App\Libraries\CafeVariome\Net\CellBaseNetworkInterface;
use App\Libraries\CafeVariome\Net\VariantValidatorNetworkInterface;

class PhenoPacketDataInput extends DataInput
{
	private array $EAVRecords; // To hold EAV records while iterating over the file
    private $data;
    private $meta;
    private string $subject_id_string;
    protected array $configuration;

    public function __construct(Task $task, int $source_id)
    {
		parent::__construct($task, $source_id);
        $this->InitializePipeline();
		$this->EAVRecords = [];
		$this->processedRecords = 0;
		$this->totalRecords = 0;
    }

    public function Absorb(int $file_id): bool
    {
		$dataFile = $this->dataFileAdapter->Read($file_id);
		if ($dataFile->isNull())
		{
			return false;
		}
		else
		{
			$this->fileName = $dataFile->disk_name;
			$this->applyPipeline($this->pipelineId);

			if ($this->fileMan->Exists($this->fileName))
			{
				$fileContent = $this->fileMan->Read($this->fileName);
				$this->data = json_decode($fileContent, true);

				if($this->overwrite == UPLOADER_DELETE_FILE)
				{
					$this->ReportProgress(0, 'Deleting existing data for the file');
					$this->DeleteExistingRecords($file_id);
				}

				$this->meta = null;

				if (array_key_exists("metaData", $this->data))
				{
					$this->meta = [];
					$target = &$this->data['metaData']['resources'];

					for ($i=0; $i < count($target); $i++)
					{
						$this->meta[$target[$i]['namespacePrefix']] = [];
						$this->meta[$target[$i]['namespacePrefix']]['meta_name'] = $target[$i]['name'];
						// Some Ontologies (like OMIM) dont have versions
						if (isset($target[$i]['version']))
						{
							$this->meta[$target[$i]['namespacePrefix']]['meta_version'] = $target[$i]['version'];
						}
					}
				}

				if($this->configuration['subject_id_location'] == SUBJECT_ID_WITHIN_FILE)
				{
					$this->subject_id_string = $this->data['subject']['id'];
				}
				else if($this->configuration['subject_id_location'] == SUBJECT_ID_IN_FILE_NAME)
				{
					preg_match("/(.*)\./", $dataFile->name, $matches);
					$this->subject_id_string = $matches[1];
				}
				return true;
			}
			return false;
		}

		return false; // If the control reaches this section, the file has not been absorbed successfully.
    }

    public function Save(int $file_id): bool
    {
		try
		{
			$this->sourceAdapter->Lock($this->sourceId);

			$this->db->begin_transaction();
			$this->ReportProgress(0, 'Reading file');

			$done = ['meta' => [],'negated' => ['true' => 0],'cell' => [], "type" => []];

			$subject_id = $this->getSubjectIdByName($this->subject_id_string);
			// perform recursive insert for given file
			$done = $this->recursivePacket($this->data, $this->meta, null, false, false, $done);

			$group_id = $this->getRandomString();
			$this->readEAV('type', 'ancestor', $group_id);

			$this->ReportProgress(100);
			$this->ReportProgress(0, 'Importing data');

			foreach ($this->EAVRecords as &$groups)
			{
				$group_name = implode('_', array_keys($groups));
				$group_id = $this->getGroupIdByName($group_name);
				foreach ($groups as $attribute => &$values)
				{
					$attribute_id = $this->getAttributeIdByName($attribute);
					foreach ($values as &$value)
					{
						$value_id = $this->getValueIdByNameAndAttributeId($value, $attribute);
						$this->createEAV($group_id, $file_id, $subject_id, $attribute_id, $value_id);
						$this->processedRecords++;
						$this->ReportProgress();
					}
				}

			}

			$dbRet = $this->db->commit();

			if ($dbRet === false)
			{
				$message = "Data Failed to insert. Please double check file for sanity.";
				return false;
			}

			//Update value frequencies
			$this->updateValueFrequencies();

			//Set attributes types, minimum, and maximum values if applicable
			$this->determineAttributesType();

			//Determine storage location of each attribute
			$this->determineAttributesStorageLocation();

			return true;
		}
		catch (\Exception $ex)
		{
			return false;
		}
    }

	/**
	 * Recursive Packet - Up to Date Recursive Loop to iterate through a multi nested
	 * array created from json_decoding a phenoPacket and preparing arrays
	 * to insert into mysql - Table eavs
	 *
	 * @param array $array - The array we are currently iterating through
	 * @param array $meta - The list of metaData tags,versions and names listed in the phenoPacket
	 * @param string|null $uid - The md5 ID linking groups together
	 * @param string $type - The Type of the current nested array
	 * @param boolean $one_group - To determine whether all nested array from here must remain in the same group
	 * @param array $done - Supplementary information to ensure we do not duplicate meta rows and
	 *                               correctly add negated 0 if necessary
	 * @return array $done       - See above
	 */
    private function recursivePacket(array $array, ?array $meta, ?string $group_id, string $type, bool $one_group, array &$done)
	{
        foreach($array as $key => $value)
		{
            if ($key == "id" && $value == $this->subject_id_string)
			{
                continue;
            }
            if(is_array($value))
			{
                if (is_numeric($key))
				{
                    // Since we are starting a new group we need to check if the previous group had a
                    // negated added to it. If it hasn't we need to add negated 0
                    // However we also need to make sure that the uid is present as this could be the
                    // very first group being created
					$group_id = $this->getRandomString();
                    // Since this is an array key all sub arrays remain part of the same group
                    // One group is set to true to ensure this
                    $one_group = true;

                    // reset done array to keep track of whether negated has been added and which meta rows have been added
                    if (!in_array($type, $done['type']))
					{
						$this->readEAV('type', $type, $group_id);
                        array_push($done['type'], $type);
                    }

                    $done = $this->recursiveNumeric($value, $meta, $group_id, $type, $one_group, $done);
                    continue;
                }
                else
				{
                    // We don't want to add any rows in this sub array
                    // if ($key = "evidence") {
                    // 	$one_group = true;
                    // }
                    $one_group = false;
                    if ($key == "metaData")
					{
                        continue;
                    }
                    if ($key != "type")
					{
                        $type = $key;
                    }
                }
                $done = $this->recursivePacket($value, $meta, $group_id, $type, $one_group, $done);
            }
            else
			{
                // We are making a group which wasn't in an indexed array
                if (!$one_group)
				{
					$group_id = $this->getRandomString();
                    $one_group = true;
                    //$done = ['meta' => [],'negated' => ['true' => 0],'cell' => [], "type" => []];
                    if (is_array($type) && !in_array($type, $done['type']))
					{
						$this->readEAV('type', $type, $group_id);
                        array_push($done['type'], $type);
                    }
                }
                // Since we are combining type with the key for attribute
                // For negated we would just like it on its own.
                // Also set negated true as we have indeed added a negated row
                if ($key == 'negated')
				{
                    $string = $key;
                    $done['negated']['true'] = 1;
                    if ($value === false)
					{
                        $value = 0;
                    }
                }
                else
				{
                    $string = $type."_".$key;
                    // $string = $key;
                }

				$this->readEAV($string, $value, $group_id);

                // Since we have just an id row we need to check its id group with our list of meta
                // attributes (if applicable)
                if ($key == "id")
				{
                    $prefix = explode(":", $value);
                    $prefix = $prefix[0];
                    if ($meta)
					{
                        if (array_key_exists($prefix, $meta))
						{
                            // Since it exists now check if we have already added these meta rows before to this group
                            if (!in_array($prefix, $done['meta']))
							{
								$this->readEAV('meta_name', $meta[$prefix]['meta_name'], $group_id);
                                // Make sure we do have a version for this meta
                                if (isset($meta[$prefix]['meta_version']))
								{
									$this->readEAV('meta_version', $meta[$prefix]['meta_version'], $group_id);
                                }

                                array_push($done['meta'], $prefix);
                            }
                        }
                    }
                }
            }
        }
        // Pass the current uid in use to the negated check to the array above so
        // we know which group to add negated 0 to (if applicable)
        $done['negated']['uid'] = $group_id;
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
     * @param string $group_id        - The md5 ID linking groups together
     * @param string $type       - The Type of the current nested array
     * @param boolean $one_group - To determine whether all nested array from here must remain in the same group
     * @param array $done        - Supplementary information to ensure we do not duplicate meta rows and
     *							   correctly add negated 0 if necessary
    * @return array $done       - See above
    */
    private function recursiveNumeric(array $array, ?array $meta, string $group_id, $type, bool $one_group, array &$done)
	{
        // prep for variants api call
        if ($type == "variants")
		{
            list( $assembly, $chrom, $coordinateSystem, $pos, $ref, $alt ) = array( "","","","","","");
        }

        foreach($array as $key => $value)
		{
            if ($key == "id" && $value == $this->subject_id_string)
			{
                continue;
            }

            if(is_array($value))
			{
                if ($type == "variants")
				{
                    if (!in_array($pos, $done['cell']))
					{
                        $compact = compact("assembly","chrom","coordinateSystem","pos","ref","alt");
                        $pos = $this->cellBaseApi($compact);
                        array_push($done['cell'], $pos);
                    }
                }

                if (is_numeric($key))
				{
					$one_group = true;
					if (!in_array($type, $done['type']))
					{
						$this->readEAV('type', $type, $group_id);
						array_push($done['type'], $type);
					}
                }
                else
				{
                    if ($key == "metaData")
					{
                        continue;
                    }
                    if ($key != "type")
					{
                        $type = $key;
                        if ($group_id)
						{
                            if (!in_array($type, $done['type']))
							{
								$this->readEAV('type', $type, $group_id);
								array_push($done['type'], $type);
                            }
                        }

                    }
                }

				if ($type == 'phenotypicFeatures' && $key == 'type')
				{
					if (array_key_exists('negated',$array))
					{
						if ($array['negated'] == true)
						{
							$this->readEAV('negated' . $type, $value['id'], $group_id);
						}
						else
						{
							$this->readEAV($type, $value['id'], $group_id);
						}
					}
					else
					{
						$this->readEAV($type, $value['id'], $group_id);
					}

					if (!in_array($type, $done['type']))
					{
						$this->readEAV('type', $type, $group_id);
						array_push($done['type'], $type);
					}

					return $done;
				}
                $done = $this->recursiveNumeric($value, $meta, $group_id, $type, $one_group,$done);
            }
            else
			{
                if ($type == "variants")
				{
                    switch($key)
					{
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
                    if ($this->mempty($assembly, $chrom, $coordinateSystem, $pos, $ref, $alt))
					{
                        if (!in_array($pos, $done['cell']))
						{
                            $compact = compact("assembly","chrom","coordinateSystem","pos","ref","alt");
                            $pos = $this->cellBaseApi($compact);
                            array_push($done['cell'], $pos);
                        }
                    }
                }
				if ($key == 'negated')
				{
					return $done;
				}

				$string = $type . "_" . $key;
                // We were passing the incorrect value to this function. Causing incorrect addition to database
                // see line 229 for correct example in recursivePacket()
				$this->readEAV($string, $value, $group_id);
                // Since we have just an id row we need to check its id group with our list of meta
                // attributes (if applicable)
                if ($key == "id")
				{
                    $prefix = explode(":", $value);
                    $prefix = $prefix[0];
                    if ($meta)
					{
                        if (array_key_exists($prefix, $meta))
						{
                            // Since it exists now check if we have already added these meta rows before to this group
                            if (!in_array($prefix, $done['meta']))
							{
								$this->readEAV('meta_name', $meta[$prefix]['meta_name'], $group_id);
                                if (isset($meta[$prefix]['meta_version']))
								{
									$this->readEAV('meta_version', $meta[$prefix]['meta_version'], $group_id);
                                }

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
    private function cellBaseApi(array $compact)
	{

        $cellBaseApi = new CellBaseNetworkInterface();
        $variantValidatorApi = new VariantValidatorNetworkInterface();

        extract($compact);

        if(empty($chrom) && empty($pos))
		{

        }
        if ($coordinateSystem == "ONE_BASED" || $coordinateSystem == 1)
		{

        }
        elseif ($coordinateSystem == "ZERO_BASED" || $coordinateSystem == 0 || $coordinateSystem == "")
		{
            $pos++;
        }
        if ($assembly == "GRCH_37")
		{
            $assembly = "GRCh37";
        }
        elseif ($assembly == "GRCH_38")
		{
            $assembly = "GRCh38";
        }
        $posLength = strlen($ref);
        $posEnd = $pos + $posLength -1;
        $possibleRef = $ref;


        if (!is_null($variantValidatorApi->ValidateVariant($chrom, $pos, $ref, $alt, $assembly)))
		{
            $message = "Variant is not valid, as according to VariantValidator. https://variantvalidator.org/";
            $error_code = 3;
            return;
        }

        $results = $cellBaseApi->AnnotateVariant($chrom, $pos, $ref, $alt, $assembly);
        $results = $results->response[0]->result[0]->consequenceTypes;
        $output = [];
        // $counter = 0;
        for ($i=0; $i <count($results) ; $i++)
		{
            if (!isset( $results[$i]->geneName))
			{
                continue;
            }
            $geneName = $results[$i]->geneName;
            if (!array_key_exists($geneName, $output))
			{
                $output[$geneName] = [];
                $output[$geneName]['geneName'] = $geneName;
                $output[$geneName]['ensemblGeneId'] = $results[$i]->ensemblGeneId;
            }
            $string = "";
            $first = true;
            for ($t=0; $t < count($results[$i]->sequenceOntologyTerms) ; $t++)
			{
                if (!array_key_exists($results[$i]->sequenceOntologyTerms[$t]->name, $output[$geneName]))
				{
                    if ($first)
					{
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

        foreach ($output as $key => $value)
		{
			$group_id = $this->getRandomString();

			$this->readEAV('genomeAssembly', $assembly, $group_id);
			$this->readEAV('sequence', $chrom, $group_id);
			$this->readEAV('coordinateSystem', $coordinateSystem, $group_id);
			$this->readEAV('position', $pos, $group_id);
			$this->readEAV('deletion', $ref, $group_id);
			$this->readEAV('insertion', $alt, $group_id);

            $this->recursiveCell($output[$key],$group_id);
        }
        return $pos;
    }

    /**
     * Recursive Cell - Recursive Loop to overwrite data into eavs of all data from all CellBase Data
     *
     * @param array $array - Array we are adding from
     * @param string $group_id  - The group id we are adding to
     */
    private function recursiveCell(array $array, string $group_id)
	{
        foreach($array as $key => $value)
		{
            if(is_array($value))
			{
                $this->recursiveCell($value,$group_id);
            }
            else
			{
				$this->readEAV($key, $value, $group_id);
            }
        }
    }

    /**
     * Mempty - Checks all parameters (variable amount)
     * If any are empty return false. If all have content return true.
     */
    public function mempty(): bool
	{
        foreach(func_get_args() as $arg)
		{
            if(empty($arg))
			{
                return false;
            }
        }
        return true;
    }


	protected function InitializePipeline()
	{
		$this->configuration = [
			'subject_id_location' => SUBJECT_ID_WITHIN_FILE,
			'subject_id_attribute_name' => 'id'
		];
	}

    protected function applyPipeline(int $pipeline_id)
    {
        $pipeline = $this->piplineAdapter->Read($pipeline_id);

        if (!$pipeline->isNull())
		{
            $this->configuration['subject_id_location'] = $pipeline->subject_id_location;

            if ($pipeline->subject_id_location == SUBJECT_ID_WITHIN_FILE && $pipeline->subject_id_attribute_name != null && $pipeline->subject_id_attribute_name != '')
			{
                $this->configuration['subject_id_attribute_name'] = $pipeline->subject_id_attribute_name;
            }
        }
    }

	private function readEAV(string $attribute, string $value, string $group)
	{
		if ($attribute == '' || $value == '')
		{
			return;
		}

		if (array_key_exists($group, $this->EAVRecords))
		{
			if (array_key_exists($attribute, $this->EAVRecords[$group]))
			{
				$this->EAVRecords[$group][$attribute][] = $value;
			}
			else
			{
				$this->EAVRecords[$group][$attribute] = [$value];
			}
		}
		else
		{
			$this->EAVRecords[$group][$attribute] = [$value];
		}
		$this->totalRecords++;
	}

	private function getRandomString(): string
	{
		return bin2hex(random_bytes(4));
	}
}
