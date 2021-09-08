<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Input;

/**
 * Name SpreadsheetDataInput.php
 *
 * Created 19/08/2020
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 *
 */

use App\Models\EAV;

class SpreadsheetDataInput extends DataInput
{
    private $delete;
    private $error;
    protected $serviceInterface;
    protected $configuration;
    private $column_count;
    protected $pipeline_id;

    public function __construct(int $source_id, int $delete) {
        parent::__construct($source_id);
        $this->delete = $delete;
        $this->initializeConfiguration();
    }

    public function absorb(int $file_id){

        $this->registerProcess($file_id);

        $fileRecord = $this->getSourceFiles($file_id); //Get a list of files for source

        if (count($fileRecord) == 1) {
            $file = $fileRecord[0]['FileName'];
            $this->fileName = $file;
            if (array_key_exists('pipeline_id', $fileRecord[0])) {
                $this->pipeline_id = $fileRecord[0]['pipeline_id'];
                $this->applyPipeline($this->pipeline_id);
            }

            if ($this->fileMan->Exists($file)) {
                $this->uploadModel->clearErrorForFile($file_id);
                $this->sourceModel->lockSource($this->sourceId);

                if ($this->delete == UPLOADER_DELETE_ALL) {
                    $this->reportProgress($file_id, 0, 1, 'bulkupload', 'Deleting existing data');
                    $this->eavModel->deleteRecordsBySourceId($this->sourceId);
                }
                elseif ($this->delete == UPLOADER_DELETE_FILE) {
                    $this->reportProgress($file_id, 0, 1, 'bulkupload', 'Deleting existing data for the file');
                    $this->eavModel->deleteRecordsByFileId($file_id);
                }

                $filePath = $this->basePath . $file;

                $return_data = array('result_flag' => 1);
                if (preg_match("/\.csv$|\.tsv$/", $file)) {
                    $line = fgets(fopen($filePath, 'r'));
                    preg_match("/^" . $this->configuration['subject_id_attribute_name'] . "(.)/", $line, $matches);

                    $delimiter = $matches[1];

                    $this->reader = \Box\Spout\Reader\ReaderFactory::create(\Box\Spout\Common\Type::CSV);
                    $this->reader->setFieldDelimiter($delimiter);
                }
                elseif (preg_match("/\.xlsx$/", $file)) {

                    $this->reader = \Box\Spout\Reader\ReaderFactory::create(\Box\Spout\Common\Type::XLSX);
                }
                elseif (preg_match("/\.ods$/", $file)) {
                    $this->reader = \Box\Spout\Reader\ReaderFactory::create(\Box\Spout\Common\Type::ODS);
                }
                else {
                    $return_data['result_flag'] = 0;
                    $return_data['error'] = "File did not conform to allowed types";
                    $message = "File did not conform to allowed types.";
                    $error_code = 2;
                    $this->uploadModel->errorInsert($file_id, $message, $error_code, true);
                    //return $return_data;
                }

                $this->reader->open($filePath);
            }
        }
    }

    public function save(int $file_id)
    {
        $this->reportProgress($file_id, 0, 1, 'bulkupload', 'Counting records');

        $recordCount = $this->countRecords();

        $this->reportProgress($file_id, 0, $recordCount, 'bulkupload', 'Importing data');

        list($linerow, $counter) = array(1, 0);

        if ($this->configuration['subject_id_location'] == SUBJECT_ID_IN_FILE_NAME) {
            if (strpos($this->fileName, '.')) {
                $subject_id = explode('.', $this->fileName)[0];
            }
        }

        foreach ($this->reader->getSheetIterator() as $sheet) {
            $recordsProcessed = -1;  // set counter to -1 initially to avoid counting header of the file

            $attgroups = [];
            $temphash = [];

            foreach ($sheet->getRowIterator() as $row) {
                if ($recordsProcessed == -1) {
                    if (!$this->checkHeader($file_id, $row, $attgroups, $temphash)){
                        break;
                    }
                    $this->db->begin_transaction();
                }
                else {
                    if ($this->configuration['subject_id_location'] == SUBJECT_ID_WITHIN_FILE) {
                        $subject_id = $row[0];
                    }
                    if ($subject_id == ""){
                        $message = "All records require a record ID, a record on line:".$linerow." in the import data that do not have a record ID, please add record IDs to all records and re-try the import.";
                        $error_code = 3;
                        $this->uploadModel->errorInsert($file_id, $this->sourceId, $message, $error_code, true);
                        $this->sourceModel->unlockSource($this->sourceId);
                    }
                    $this->processRow($row, $attgroups, $subject_id, $file_id, $counter);
                }
                $recordsProcessed++;

                $this->reportProgress($file_id, $recordsProcessed, $recordCount, 'bulkupload');
            }

            $linerow++;
        }

        $this->reader->close();

        $this->db->commit();

        if ($this->delete == 1) {
            $this->removeAttribuesAndValuesFiles($this->fileName);
        }

        $this->dumpAttributesAndValues($file_id);
    }

	private function processRow($row, $attgroups, $subject_id, $file_id, & $counter)
	{
		foreach ($attgroups as $group){
			$uid = md5(uniqid(rand(),true));
			foreach ($group as $att => $val){
				$att = strtolower(preg_replace('/\s+/', '_', $att));
				$value = strtolower($row[$val]);
				if ($value == "") continue;
				if (is_a($value, 'DateTime')) $value = $value->format('Y-m-d H:i:s');

				if ($this->configuration['internal_delimiter'] != '' && $this->configuration['internal_delimiter'] != null) {
					//if there is an internal delimiter, split the value on it and insert subvalues individually
					$internal_delimiter = $this->configuration['internal_delimiter'];

					if (strpos($value, $internal_delimiter)) {
						$value_array = explode($internal_delimiter, $value);

						foreach ($value_array as $sub_value) {
							$this->createEAV($uid, $this->sourceId, $file_id, $subject_id, $att, $sub_value);
						}
					}
					else {
						$this->createEAV($uid, $this->sourceId, $file_id, $subject_id, $att, $value);
					}
				}
				else {
					$this->createEAV($uid, $this->sourceId, $file_id, $subject_id, $att, $value);
				}

				$counter++;
				if ($counter % SPREADSHEET_BATCH_SIZE == 0) {
					$error = $this->sendBatch();
					if ($error) {
						$error_code = 0;
						$message = "MySQL insert was unsuccessful.";
						$this->uploadModel->errorInsert($file_id, $this->sourceId, $message, $error_code, true);
						$this->sourceModel->unlockSource($this->sourceId);
					}
				}
			}
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

       if (!$status){
       	return !$status;
	   }

       $this->db->begin_transaction();
    }

    private function countRecords(): int
    {
        $rc = -1; // set counter to -1 initially to avoid counting header of the file
        foreach ($this->reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
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

        for ($i=1; $i < $this->column_count; $i++) {
            $positions[$i] = $i;
        }

        return $positions;
    }

    protected function initializeConfiguration()
    {
        $this->configuration = ['subject_id_location' => SUBJECT_ID_WITHIN_FILE,
                                'subject_id_attribute_name' => 'subject_id',
                                'grouping_policy' => GROUPING_COLUMNS_ALL, // 0 -> separate all columns with <group_end>, 1 -> custom: define <group_end> positions
                                'group_end_positions' => [],
                                'internal_delimiter' => ''
        ];
    }

    protected function applyPipeline(int $pipeline_id)
    {
        $pipeline = $this->pipelineModel->getPipeline($pipeline_id);

        if ($pipeline != null) {
            $this->configuration['subject_id_location'] = $pipeline['subject_id_location'];
            $this->configuration['grouping_policy'] = $pipeline['grouping'];

            if ($pipeline['subject_id_location'] == SUBJECT_ID_WITHIN_FILE && $pipeline['subject_id_attribute_name'] != null && $pipeline['subject_id_attribute_name'] != '') {
                $this->configuration['subject_id_attribute_name'] = $pipeline['subject_id_attribute_name'];
            }

            if ($pipeline['grouping'] == GROUPING_COLUMNS_CUSTOM && $pipeline['group_columns'] != null && $pipeline['group_columns'] != '') {
                $this->configuration['grouping_policy'] = $pipeline['grouping'];
                $this->configuration['group_end_positions'] = $pipeline['group_columns'];
            }

            $valid_delimiters = [',', '/', ';', ':', '|', '*', '&', '%', '$', '!', '~', '#', '-', '_', '+', '=', '^'];

            if ($pipeline['internal_delimiter'] != null && $pipeline['internal_delimiter'] != '' && in_array($pipeline['internal_delimiter'], $valid_delimiters)) {
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
        for ($i=0; $i < count($row); $i++) {
            if ($i === 0) //check for existence subject id as the first column in header, if necessary
            {
                if ($this->configuration['subject_id_location'] == SUBJECT_ID_WITHIN_FILE && $row[$i] != $this->configuration['subject_id_attribute_name']){
                    $message = "No " . $this->configuration['subject_id_attribute_name'] . " column.";
                    $error_code = 1;
                    $this->reportError($file_id, $error_code, $message);

                    return false;
                }
                continue;
            }
            $temphash[$row[$i]] = $i;

            if (in_array($i , $group_positions)){
                if (count($temphash) > 0){
                    $attgroups[$groupnumber] = $temphash;
                    $groupnumber++;
                    $temphash = [];
                }
            }
            if (count($temphash) > 0){
                $attgroups[$groupnumber] = $temphash;
            }
        }

        return ($i > 1) ? true : false;
    }

    protected function reportError(int $file_id, int $error_code, string $message)
    {
        $this->uploadModel->errorInsert($file_id, $this->sourceId, $message, $error_code, true);
        $this->sourceModel->unlockSource($this->sourceId);

        //report to service
        //$this->serviceInterface->ReportError();
    }
}
