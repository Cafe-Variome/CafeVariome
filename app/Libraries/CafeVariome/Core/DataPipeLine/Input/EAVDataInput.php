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

    public function __construct(int $source_id, int $delete) {
        parent::__construct($source_id);
        $this->delete = $delete;
        $this->serviceInterface = new ServiceInterface();
    }

    public function absorb(int $file_id){

        $files = $this->getSourceFiles($file_id); //Get a list of files for source

        foreach ($files as $key => $fname) {

            $fileId = $file_id != -1 ? $file_id : $key;
            $file = $fname['FileName'];

            if ($this->fileMan->Exists($file)) {
                $this->uploadModel->clearErrorForFile($fileId);
        
                if ($this->delete == 1) {		
                    $this->sourceModel->deleteSourceFromEAVs($this->sourceId);
                }
                
                $filePath = $this->basePath . $file;

                $return_data = array('result_flag' => 1);   
                $attgroups = [];
                if (preg_match("/\.csv$|\.tsv$/", $file)) {
                    $line = fgets(fopen($filePath, 'r'));
                    if (!preg_match("/^subject_id(.)/", $line, $matches)) {
                        $return_data['result_flag'] = 0;
                        $return_data['error'] = 'No subject_id column.';
                        $message = 'No subject_id column.';
                        $error_code = 1;
                        $this->uploadModel->errorInsert($fileId,$this->sourceId,$message,$error_code,true);
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
                    $this->uploadModel->errorInsert($fileId,$message,$error_code,true);         
                    return $return_data;
                }
        
                $this->sourceModel->toggleSourceLock($this->sourceId);	
                $this->reader->open($filePath);
            }
        }
    }

    public function save(int $file_id)
    { 
        $recordCount = $this->countRecords();
        $this->serviceInterface->RegisterProcess($file_id, $recordCount, 'bulkupload');

        list($true, $linerow, $counter, $groupnumber) = array(true, 1, 0, 0);

        foreach ($this->reader->getSheetIterator() as $sheet) {
            $recordsProcessed = -1;  // set counter to -1 initially to avoid counting header of the file

            foreach ($sheet->getRowIterator() as $row) {
                if ($true) {			 
                    for ($i=0; $i < count($row); $i++) { 	
                        if ($i === 0) {
                            if ($row[$i] != "subject_id"){
                                $return_data['result_flag'] = 0;
                                $return_data['error'] = "No subject_id column.";
                                $message = "No subject_id column.";
                                $error_code = 1;
                                $this->uploadModel->errorInsert($file_id,$this->sourceId,$message,$error_code,true);
                                $this->sourceModel->toggleSourceLock($this->sourceId);
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
                        $this->uploadModel->errorInsert($file_id,$this->sourceId,$message,$error_code,true);
                        $this->sourceModel->toggleSourceLock($this->sourceId);
                        return $return_data;
                    }
                    foreach ($attgroups as $group){
                        $uid = md5(uniqid(rand(),true));                           
                        foreach ($group as $att => $val){                          
                            $value = $row[$val];
                            if ($value == "") continue;     
                            if (is_a($value, 'DateTime')) $value = $value->format('Y-m-d H:i:s');
                            $this->uploadModel->jsonInsert($uid,$this->sourceId,$file_id,$subject_id,$att,$value);
                            $counter++;                            
                            if ($counter % 800 == 0) {
                                $error = $this->sendBatch();   
                                error_log($counter);                          
                                if ($error) {                             
                                    error_log("failed on insert");
                                    $return_data['result_flag'] = 0;
                                    $return_data['error'] = "MySQL insert was unsuccessful.";

                                    $this->sourceModel->toggleSourceLock($this->sourceId);
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
        $this->sourceModel->toggleSourceLock($this->sourceId);	

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
                $rc++;
            }
        }
        return $rc;
    }

    // private function report(array $report)
    // {
    //     $address = "127.0.0.1";
    //     $service_port = "49200";

    //     $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

    //     $result = socket_connect($socket, $address, $service_port);
    
    //     socket_write($socket, json_encode($report), strlen(json_encode($report)));
    //     while ($out = socket_read($socket, 2048)) {
    //         echo $out;
    //     }
    //     socket_close($socket);
    
    //     usleep(100);
    // }
}
