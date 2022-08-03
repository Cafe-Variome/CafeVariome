<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Input;

/**
 * Name SpreadsheetDataInput.php
 *
 * Created 19/08/2020
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 *
 */

use App\Libraries\CafeVariome\Entities\Task;
use \Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class SpreadsheetDataInput extends DataInput
{
    protected $serviceInterface;
    protected array $configuration;
    private $column_count;
    protected $pipeline_id;

    public function __construct(Task $task, int $source_id)
	{
        parent::__construct($task, $source_id);
        $this->InitializePipeline();
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
				$this->sourceAdapter->Lock($this->sourceId);

				if ($this->overwrite == UPLOADER_DELETE_FILE)
				{
					$this->ReportProgress(0, false, 'Deleting existing data for the file');
					$this->DeleteExistingRecords($file_id);
				}

				$filePath = $this->basePath . $this->fileName;

				if (preg_match("/\.csv$|\.tsv$/", $this->fileName))
				{
					$line = fgets(fopen($filePath, 'r'));
					preg_match("/^" . $this->configuration['subject_id_attribute_name'] . "(.)/", $line, $matches);
					if (
						count($matches) < 2 &&
						$this->configuration['subject_id_location'] == SUBJECT_ID_WITHIN_FILE
					)
					{
						//The subject_id attribute name specified didn't exist.
						return false;
					}
					else
					{
						if (count($matches) == 2)
						{
							$delimiter = $matches[1];
						}
						else
						{
							$delimiter = $this->detectDelimiter($line);
						}

						$this->reader = ReaderEntityFactory::createReaderFromFile($filePath);
						$this->reader->setFieldDelimiter($delimiter);
					}
				}
				elseif (preg_match("/\.xlsx$/", $this->fileName))
				{
					$this->reader = ReaderEntityFactory::createReaderFromFile($filePath);
				}
				elseif (preg_match("/\.ods$/", $this->fileName))
				{
					$this->reader = ReaderEntityFactory::createReaderFromFile($filePath);
				}
				else
				{
					$message = "File did not conform to allowed types.";
					$error_code = 2;

					return false;
				}

				$this->reader->open($filePath);
				return true;
			}
		}
		return false; // If the control reaches this section, the file has not been absorbed successfully.
    }

    public function Save(int $file_id): bool
    {
		try
		{
			$subject_id = "";
			$this->ReportProgress(0, 'Counting records');
			$recordCount = $this->countRecords();
			$this->ReportProgress(0, 'Importing data');
			list($linerow, $counter) = array(1, 0);

			if ($this->configuration['subject_id_location'] == SUBJECT_ID_IN_FILE_NAME)
			{
				if (strpos($this->fileName, '.'))
				{
					$subject_id = explode('.', $this->fileName)[0];
				}
			}
			else if($this->configuration['subject_id_location'] == SUBJECT_ID_PER_FILE)
			{
				$subject_id = $this->generateSubjectId();
			}

			foreach ($this->reader->getSheetIterator() as $sheet)
			{
				$recordsProcessed = -1;  // set counter to -1 initially to avoid counting header of the file
				$attgroups = [];
				$temphash = [];

				foreach ($sheet->getRowIterator() as $row)
				{
					$attgroups_modified = null; // Only populated if row expansion is supposed to happen

					$row = $row->toArray();
					if ($recordsProcessed == -1)
					{
						if (!$this->checkHeader($file_id, $row, $attgroups, $temphash))
						{
							break;
						}
						$this->db->begin_transaction();
					}
					else
					{
						if ($this->configuration['subject_id_location'] == SUBJECT_ID_WITHIN_FILE)
						{
							$subject_id = $row[0];
						}
						else if($this->configuration['subject_id_location'] == SUBJECT_ID_PER_BATCH_OF_RECORDS)
						{
							if ($recordsProcessed % $this->configuration['subject_id_assigment_batch_size'] == 0)
							{
								$subject_id = $this->generateSubjectId($this->configuration['subject_id_prefix']);
							}
						}
						if ($subject_id == "" && $this->configuration['subject_id_location'] != SUBJECT_ID_BY_EXPANSION_ON_COLUMNS)
						{
							$message = "All records require a record ID, a record on line: " . $linerow . " in the import data that do not have a record ID, please add record IDs to all records and re-try the import.";
							$error_code = 3;

							$this->sourceAdapter->Unlock($this->sourceId);

							return false;
						}

						if (
							$subject_id == "" &&
							$this->configuration['subject_id_location'] == SUBJECT_ID_BY_EXPANSION_ON_COLUMNS
						)
						{
							$expansion_index = -1;
							$expansion_attribute = '';
							$rows_to_expand = 0;
							$expansion_attributes_to_remove = [];
							$expansion_attributes = $this->getExpansionAttributes();

							$this->getExpansionDetails($row, $expansion_index, $rows_to_expand, $expansion_attributes_to_remove);

							if ($attgroups_modified == null)
							{
								$new_group_number = 0;
								// Remove attributes that are used for expansion
								foreach ($attgroups as $group_array)
								{
									$temp_group = [];
									foreach ($group_array as $attribute => $val)
									{
										if (!in_array($val, $expansion_attributes))
										{
											$temp_group[$attribute] = $val;
										}
										if($val == $expansion_index)
										{
											$expansion_attribute = $attribute;
										}
									}
									if(count($temp_group) > 0)
									{
										$attgroups_modified[$new_group_number++] = $temp_group;
									}
								}
								$attgroups_modified[] = [$this->configuration['expansion_attribute_name'] => $expansion_index];
							}

							// Remove expansion attributes that have not been picked up
							foreach ($expansion_attributes_to_remove as $expansion_attribute_to_remove)
							{
								unset($row[$expansion_attribute_to_remove]);
							}

							// Create new rows to expand on the expansion attribute
							for($c = 0; $c < $rows_to_expand; $c++)
							{
								$subject_id = $this->generateSubjectId();

								if ($this->configuration['expansion_policy'] == SUBJECT_ID_EXPANDSION_POLICY_INDIVIDUAL)
								{

									$row[$expansion_index] = 1;
								}
								else
								{
									$row[$expansion_index] = $expansion_attribute;
								}

								$this->processRow($row, $attgroups_modified, $subject_id, $file_id, $counter);
							}
							$subject_id = "";
						}
						else
						{
							$this->processRow($row, $attgroups, $subject_id, $file_id, $counter);
						}
					}
					$recordsProcessed++;

					$progress =  intval(ceil((($recordsProcessed / $recordCount)) * 100.0));
					$this->ReportProgress($progress);
				}
				$linerow++;
			}

			$this->reader->close();
			$this->db->commit();

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

	private function processRow(array $row, array $attgroups, string $subject_id_string, int $file_id, int & $counter)
	{
		foreach ($attgroups as $groupArray)
		{
			$group = implode('_', array_keys($groupArray));
			$group_id = $this->getGroupIdByName($group);
			$groupAttributeIds = [];

			foreach ($groupArray as $attribute => $val)
			{
				$value = trim($row[$val]);
				if ($value == "") continue; // Skip empty values

				if (is_a($value, 'DateTime'))
				{
					$value = $value->format('Y-m-d H:i:s');
				}

				$subject_id = $this->getSubjectIdByName($subject_id_string);
				$attribute_id = $this->getAttributeIdByName($attribute);
				array_push($groupAttributeIds, $attribute_id);

				if ($this->configuration['internal_delimiter'] != '' && $this->configuration['internal_delimiter'] != null)
				{
					//if there is an internal delimiter, split the value on it and insert subvalues individually
					$internal_delimiter = $this->configuration['internal_delimiter'];

					if (strpos($value, $internal_delimiter) !== false)
					{
						$value_array = explode($internal_delimiter, $value); // Split compound value to sub_values

						foreach ($value_array as $sub_value)
						{
							$sub_value_id = $this->getValueIdByNameAndAttributeId($sub_value, $attribute);
							$this->createEAV($group_id, $file_id, $subject_id, $attribute_id, $sub_value_id);
						}
					}
					else
					{
						$value_id = $this->getValueIdByNameAndAttributeId($value, $attribute);
						$this->createEAV($group_id, $file_id, $subject_id, $attribute_id, $value_id);
					}
				}
				else
				{
					$value_id = $this->getValueIdByNameAndAttributeId($value, $attribute);
					$this->createEAV($group_id, $file_id, $subject_id, $attribute_id, $value_id);
				}

				$counter++;
				if ($counter % SPREADSHEET_BATCH_SIZE == 0)
				{
					$error = $this->sendBatch();
					if ($error)
					{
						$error_code = 0;
						$message = "MySQL insert was unsuccessful.";

						$this->sourceAdapter->Unlock($this->sourceId);
					}
				}
			}

			$this->associateGroupWithAttributes($group, $groupAttributeIds);
		}
	}

    /**
    * Send Batch - Fill in the status table on the success of the upload
    *
    * @param N/A
    * @return boolean $error - True if the transaction failed
    */
    private function sendBatch()
	{
       $status = $this->db->commit();

       if (!$status)
	   {
		   return !$status;
	   }

       $this->db->begin_transaction();
    }

    private function countRecords(): int
    {
        $rc = -1; // set counter to -1 initially to avoid counting header of the file
        foreach ($this->reader->getSheetIterator() as $sheet)
		{
            foreach ($sheet->getRowIterator() as $row)
			{
				$row = $row->toArray();
                $this->column_count = count($row);
                $rc++;
            }
        }

        return $rc;
    }

    private function getGroupPositions(): array
    {
        return $this->configuration['grouping_policy'] == GROUPING_COLUMNS_ALL? $this->getDefaultGroupPositions() : $this->generateGroupPositions($this->configuration['group_end_positions']);
    }

    private function getDefaultGroupPositions(): array
    {
        $positions = [];
		$i = $this->configuration['subject_id_location'] == SUBJECT_ID_WITHIN_FILE ? 1 : 0;
        for (; $i < $this->column_count; $i++)
		{
            $positions[$i] = $i;
        }

        return $positions;
    }

    protected function InitializePipeline()
    {
        $this->configuration = [
			'subject_id_location' => SUBJECT_ID_WITHIN_FILE,
			'subject_id_attribute_name' => 'subject_id',
			'subject_id_assigment_batch_size' => 1,
			'subject_id_prefix' => '',
			'grouping_policy' => GROUPING_COLUMNS_ALL, // 0 -> separate all columns with <group_end>, 1 -> custom: define <group_end> positions
			'group_end_positions' => [],
			'internal_delimiter' => ''
        ];
    }

    protected function applyPipeline(int $pipeline_id)
    {
        $pipeline = $this->pipelineModel->getPipeline($pipeline_id);

        if ($pipeline != null)
		{
            $this->configuration['subject_id_location'] = $pipeline['subject_id_location'];
            $this->configuration['grouping_policy'] = $pipeline['grouping'];

            if (
				$pipeline['subject_id_location'] == SUBJECT_ID_WITHIN_FILE &&
				$pipeline['subject_id_attribute_name'] != null &&
				$pipeline['subject_id_attribute_name'] != ''
			)
			{
                $this->configuration['subject_id_attribute_name'] = $pipeline['subject_id_attribute_name'];
            }
			else if($pipeline['subject_id_location'] == SUBJECT_ID_PER_BATCH_OF_RECORDS)
			{
				$this->configuration['subject_id_assigment_batch_size'] = $pipeline['subject_id_assignment_batch_size'];
				$this->configuration['subject_id_prefix'] = $pipeline['subject_id_prefix'];
			}
			else if($pipeline['subject_id_location'] == SUBJECT_ID_BY_EXPANSION_ON_COLUMNS)
			{
				$this->configuration['expansion_policy'] = $pipeline['expansion_policy'];
				$this->configuration['expansion_columns'] = $pipeline['expansion_columns'];
				$this->configuration['expansion_attribute_name'] = $pipeline['expansion_attribute_name'];
			}

            if (
				$pipeline['grouping'] == GROUPING_COLUMNS_CUSTOM &&
				$pipeline['group_columns'] != null &&
				$pipeline['group_columns'] != ''
			)
			{
                $this->configuration['grouping_policy'] = $pipeline['grouping'];
                $this->configuration['group_end_positions'] = $pipeline['group_columns'];
            }

            $valid_delimiters = [',', '/', ';', ':', '|', '*', '&', '%', '$', '!', '~', '#', '-', '_', '+', '=', '^'];

            if (
				$pipeline['internal_delimiter'] != null &&
				$pipeline['internal_delimiter'] != '' &&
				in_array($pipeline['internal_delimiter'], $valid_delimiters)
			)
			{
                $this->configuration['internal_delimiter'] = $pipeline['internal_delimiter'];
            }
        }
    }

    private function generateGroupPositions(string $grouping): array
    {
        $group_positions = [];

        if (strpos($grouping, ',')) {
            $groups = explode(',', $grouping);

            for ($i=0; $i < count($groups); $i++) {
                if (intval($groups[$i])) {
                    $group_positions[$groups[$i]] = $groups[$i];
                }
            }
        }
        else if (is_numeric($grouping)){
			$group_positions[$grouping] = $grouping;
		}

        return $group_positions;
    }

    private function checkHeader(int $file_id, array $row, & $attgroups, & $temphash): bool
    {
        $group_positions = $this->getGroupPositions();
        $groupnumber = 0;
        for ($i=0; $i < count($row); $i++)
		{
            if ($i === 0) //check for existence subject id as the first column in header, if necessary
            {
                if ($this->configuration['subject_id_location'] == SUBJECT_ID_WITHIN_FILE)
				{
					if($row[$i] != $this->configuration['subject_id_attribute_name'])
					{
						$message = "No " . $this->configuration['subject_id_attribute_name'] . " column.";
						$error_code = 1;
						$this->reportError($file_id, $error_code, $message);
						return false;
					}
					else
					{
						continue;
					}
                }
            }
            $temphash[$row[$i]] = $i;

            if (in_array($i , $group_positions))
			{
                if (count($temphash) > 0)
				{
                    $attgroups[$groupnumber] = $temphash;
                    $groupnumber++;
                    $temphash = [];
                }
            }
            if (count($temphash) > 0)
			{
                $attgroups[$groupnumber] = $temphash;
            }
        }

        return $i > 1;
    }

	private function getExpansionDetails(
		array $row,
		int & $expansion_attribute_index,
		int & $rows_to_expand,
		array & $expansion_attributes_to_remove
	)
	{
		// determine the attribute to expand on, new attribute name, number of extra rows,
		$expansion_policy = $this->configuration['expansion_policy'];

		if ($expansion_policy == SUBJECT_ID_EXPANDSION_POLICY_INDIVIDUAL)
		{
			$rows_to_expand = 1;
			$expansion_attributes_to_remove = [];
		}
		else
		{
			$expansion_attributes = $this->getExpansionAttributes();
			$expansion_values = [];
			foreach ($expansion_attributes as $expansion_column)
			{
				if (
					is_numeric($row[$expansion_column]) &&
					intval($row[$expansion_column]) == trim($row[$expansion_column]) &&
					$row[$expansion_column] > 0
				)
				{
					$expansion_values[$expansion_column] = $row[$expansion_column];
				}
			}

			if ($expansion_policy == SUBJECT_ID_EXPANDSION_POLICY_MAXIMUM)
			{
				$rows_to_expand = max($expansion_values);
				$expansion_attribute_index = array_search($rows_to_expand, $expansion_values);
			}
			else if($expansion_policy == SUBJECT_ID_EXPANDSION_POLICY_MINIMUM)
			{
				$rows_to_expand = min($expansion_values);
			}

			$expansion_attributes_to_remove = array_diff($expansion_attributes, [$expansion_attribute_index]);
		}
	}

	private function getExpansionAttributes(): array
	{
		$attributes = explode(',', $this->configuration['expansion_columns']);
		for($i = 0; $i < count($attributes); $i++)
		{
			$attributes[$i] -= 1;
		}
		return $attributes;
	}

    protected function reportError(int $file_id, int $error_code, string $message)
    {
        //report to service
        //$this->serviceInterface->ReportError();
    }
}
