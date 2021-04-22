<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Input;

/**
 * Name EAVDataInput.php
 * 
 * Created 19/08/2020
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 * 
 */

use App\Libraries\CafeVariome\Net\ServiceInterface;

class EAVDataInput extends DataInput
{
    private $delete;
    private $error;
    private $serviceInterface;
    private $configuration;
    private $column_count;

    public function __construct(int $source_id, int $delete) {
        parent::__construct($source_id);
        $this->delete = $delete;
        $this->serviceInterface = new ServiceInterface();

        $this->configuration = ['subject_id_column' => 'subject_id',
                                'grouping_policy' => 0, // 0 -> separate all columns with <group_end>, 1 -> custom: define <group_end> positions
                                'group_end_positions' => []
        ];
    }

    public function absorb(int $file_id){

        $this->serviceInterface->RegisterProcess($file_id, 1, 'bulkupload', "Starting");

        $files = $this->getSourceFiles($file_id); //Get a list of files for source

        foreach ($files as $key => $fname) {

            $fileId = $file_id != -1 ? $file_id : $key;
            $file = $fname['FileName'];

            if ($this->fileMan->Exists($file)) {
                $this->uploadModel->clearErrorForFile($fileId);
                $this->sourceModel->lockSource($this->sourceId);	
        
                if ($this->delete == 1) {		
                    $this->serviceInterface->ReportProgress($file_id, 0, 1, 'bulkupload', 'Deleting existing data');

                    $this->sourceModel->deleteSourceFromEAVs($this->sourceId);
                }
                
                $filePath = $this->basePath . $file;

                $return_data = array('result_flag' => 1);   
                $attgroups = [];
                if (preg_match("/\.csv$|\.tsv$/", $file)) {
                    $line = fgets(fopen($filePath, 'r'));
                    if (!preg_match("/^" . $this->configuration['subject_id_column'] . "(.)/", $line, $matches)) {
                        $message = "No " . $this->configuration['subject_id_column'] . " column.";
                        $return_data['result_flag'] = 0;
                        $return_data['error'] = $message;
                        $error_code = 1;
                        $this->uploadModel->errorInsert($fileId, $this->sourceId, $message, $error_code, true);
                        return $return_data;
                    }
                    else {
                        $delimiter = $matches[1];
                    }
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
                    $this->uploadModel->errorInsert($fileId, $message, $error_code, true);         
                    return $return_data;
                }
        
                $this->reader->open($filePath);
            }
        }
    }

    public function save(int $file_id)
    { 
        $this->serviceInterface->ReportProgress($file_id, 0, 1, 'bulkupload', 'Counting records');

        $recordCount = $this->countRecords();
        $group_positions = $this->getGroupPositions();

        $this->serviceInterface->ReportProgress($file_id, 0, $recordCount, 'bulkupload', 'Importing data');

        list($true, $linerow, $counter, $groupnumber) = array(true, 1, 0, 0);

        foreach ($this->reader->getSheetIterator() as $sheet) {
            $recordsProcessed = -1;  // set counter to -1 initially to avoid counting header of the file

            $temphash = [];
            foreach ($sheet->getRowIterator() as $row) {
                if ($true) {			 
                    for ($i=0; $i < count($row); $i++) { 	
                        if ($i === 0) {
                            if ($row[$i] != $this->configuration['subject_id_column'] /*"subject_id"*/){
                                $message = "No " . $this->configuration['subject_id_column'] . " column.";
                                $return_data['result_flag'] = 0;
                                $return_data['error'] = $message;
                                $error_code = 1;
                                $this->uploadModel->errorInsert($file_id, $this->sourceId, $message, $error_code, true);
                                $this->sourceModel->unlockSource($this->sourceId);
                                return $return_data;
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
                        $this->uploadModel->errorInsert($file_id, $this->sourceId, $message, $error_code, true);
                        $this->sourceModel->unlockSource($this->sourceId);
                        return $return_data;
                    }
                    foreach ($attgroups as $group){
                        $uid = md5(uniqid(rand(),true));                           
                        foreach ($group as $att => $val){                          
                            $value = $row[$val];
                            if ($value == "") continue;     
                            if (is_a($value, 'DateTime')) $value = $value->format('Y-m-d H:i:s');
                            $this->uploadModel->jsonInsert($uid, $this->sourceId, $file_id, $subject_id, $att, $value);
                            $counter++;                            
                            if ($counter % 800 == 0) {
                                $error = $this->sendBatch();   
                                error_log($counter);                          
                                if ($error) {                             
                                    error_log("failed on insert");
                                    $return_data['result_flag'] = 0;
                                    $return_data['error'] = "MySQL insert was unsuccessful.";

                                    $this->sourceModel->unlockSource($this->sourceId);
                                    return $return_data;
                                }
                            }                         
                        }
                    }
                }
                $recordsProcessed++;

                $this->serviceInterface->ReportProgress($file_id, $recordsProcessed, $recordCount, 'bulkupload');
            }

            $linerow++;
        }

        $this->reader->close();

        $totalRecordCount = $this->sourceModel->countSourceEntries($this->sourceId);

        $this->sourceModel->updateSource(['record_count' => $totalRecordCount], ['source_id' => $this->sourceId]);
        
        $this->db->transComplete();
        $this->uploadModel->insertStatistics($file_id, $this->sourceId);
        $this->uploadModel->bigInsertWrap($file_id, $this->sourceId);
        $this->uploadModel->clearErrorForFile($file_id);
        $this->sourceModel->unlockSource($this->sourceId);	

        $this->serviceInterface->ReportProgress($file_id, 1, 1, 'bulkupload', 'Finished', true);
    }

    /**
    * Send Batch - Fill in the status table on the success of the upload
    *
    * @param N/A
    * @return boolean $error - True if the transaction failed 
    */
    private function sendBatch() {
       $dbRet = $this->db->transComplete();
       // select has had some problem.
       if ($this->db->transStatus() === FALSE) {
           return true;
       }
       else {
           $this->db->transStart();
       }
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
        return $this->configuration['grouping_policy'] == 0 ? $this->getDefaultGroupPositions() : $this->configuration['group_end_positions'];
    }

    private function getDefaultGroupPositions(): array
    {
        $positions = [];

        for ($i=1; $i < $this->column_count; $i++) { 
            $positions[$i] = $i;
        }

        return $positions;
    }
}
