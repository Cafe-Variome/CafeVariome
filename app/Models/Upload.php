<?php namespace App\Models;

/**
 * Name Upload.php
 * Created 01/08/2019
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 * 
 * Upload model class that handles operations on data files.
 */

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;

 class Upload extends Model 
 {
    protected $db;
    protected $table      = 'uploaddatastatus';
    protected $builder;

    protected $primaryKey = 'id';

    public function __construct(ConnectionInterface &$db = null){

        if ($db != null) {
            $this->db =& $db;
        }
        else {
            $this->db = \Config\Database::connect();
        }
        helper('filesystem');
    }

    /**
	 * getFiles
     * 
	 * General function to get fetch data from uploaddatastatus table.
     * 
     * @author Mehdi Mehtarizadeh
	 */
	function getFiles(string $cols = null, array $conds = null, array $groupby = null, bool $isDistinct = false, int $limit = -1, int $offset = -1){
		$this->builder = $this->db->table($this->table);
		
		if ($cols) {
            $this->builder->select($cols);
        }
        if ($conds) {
            $this->builder->where($conds);
        }
        if ($groupby) {
            $this->builder->groupBy($groupby);
        }
        if ($isDistinct) {
            $this->builder->distinct();
        }
        if ($limit > 0) {
            if ($offset > 0) {
                $this->builder->limit($limit, $offset);
            }
            $this->builder->limit($limit);
        }

        $query = $this->builder->get()->getResultArray();
        return $query; 
    }

    /**
     * createUpload - Perform Initial insert into UploadDataStatus table
     * to keep track of uploaded file
     * @param string $file    - The name of the file being uploaded
     * @param string $source  - The source the file was added to
     * @return int $insert_id - The ID of updated/inserted row
     */
    public function createUpload(string $file, int $source_id, int $user_id, $tissue=false, $patient=false, $settingFile = null) {
        // Get table
        $this->builder = $this->db->table($this->table);

        // Set the current time and the source_id of the inserted row
        $now = date('Y-m-d H:i:s');
        // Check if the file has been uploaded before
        if ($this->isDuplicateFile($file,$source_id)) {
            // If it has all we need to do is to update a current row with some of the 
            // information which is current
            $data = array(
            'user_id' => $user_id,
            'uploadStart' => $now,
            'uploadEnd' => null,
            'tissue' => $tissue,
            'patient' => $patient,
            'setting_file' => $settingFile,
            'Status' => 'Pending');
            $this->builder->where('source_id', $source_id);
            $this->builder->where('FileName', $file);
            $this->builder->update($data);

            $this->builder->select('ID');
            $this->builder->where('source_id', $source_id);   
            $this->builder->where('FileName', $file);
            $query = $this->builder->get()->getResultArray();
            $insert_id = $query[0]['ID'];
        }
        else {
            // We havent seen this file/source combination before. Add whole row
            $data = array(
                'source_id' => $source_id,
                'user_id' => $user_id,
                'FileName' => $file,
                'uploadStart' => $now,
                'uploadEnd' => null,
                'Status' => 'Pending',
                'patient' => $patient,
                'setting_file' => $settingFile,
                'tissue' => $tissue);		
            $this->builder->insert($data);
            $insert_id = $this->db->insertID();
        }			
        return $insert_id;
    }

    /**
     * Get File Name - Get the File name for given ID
     * 
     * Moved to upload controller by Mehdi Mehtarizadeh (02/08/2019)
     *
     * @param int $file_id - The File ID we are trying to find name for
     * @return string File Name
     */
    public function getFileName(int $file_id) {

        $this->builder = $this->db->table($this->table);

        $this->builder->select('FileName');
        $this->builder->where('ID', $file_id);
        $query = $this->builder->get()->getResultArray();
        return ($query) ? $query[0]['FileName'] : null;
    }

    /**
     * Resets status flag for uploaded file from Success or Error to Pending.
     */
    public function resetFileStatus(int $file_id)
    {
        $data = ['Status' => 'Pending'];
        $this->builder = $this->db->table($this->table);
        $this->builder->where('ID', $file_id);
        $this->builder->update($data);
    }

    /**
     * Count Upload Job Record - Count how many jobs are associated with the given user
     *
     * @param int $user_id - The user id this job is linked to
     * @return int $count  - The number of jobs currently occuring for the given user
     */
    public function countUploadJobRecord(int $user_id) {

        $this->builder = $this->db->table('upload_jobs');

        $this->builder->select('user_id');
        $this->builder->where('user_id', $user_id);
        $count = $this->builder->countAllResults();
        return $count;
    }

    /**
     * Get File ID - Get the File ID for given source ID and file name
     *
     * @param int $source_id  - The source ID we are checking inside of
     * @param string $file - The File name we are searching for
     * @return int File_ID|null
     */
    public function getFileId(int $source_id, string $file) {
        $this->builder = $this->db->table($this->table);

        $this->builder->select('ID');
        $this->builder->where('FileName', $file);
        $this->builder->where('source_id', $source_id);
        $query = $this->builder->get()->getResultArray();
        if (count($query) == 1) {
            return $query[0]['ID'];
        }
        return null;
    }
    /**
     * Check Upload Job Record - Check if any jobs have been completed
     *
     * @param int $user_id - The user id this job is linked to
     * @return array $query - An array listing the source_id and whether the source is locked
     */
    public function checkUploadJobRecord($user_id) {

        $this->builder = $this->db->table('upload_jobs');

        $this->builder->select('upload_jobs.source_id,sources.elastic_lock');
        $this->builder->join('sources', 'upload_jobs.source_id = sources.source_id', 'inner');
        $this->builder->where('upload_jobs.user_id', $user_id);
        $query = $this->builder->get()->getResultArray();
        return $query;
    }

    /**
     * Check Json Files - Check if any of the Json files already exist on the server for
     * given source
     *
     * @param array $files   - The list of files to check
     * @param int $source_id - The source_id we are checking
     * @return array empty if no duplicates | with elements of file names if they exist
     */
    public function checkJsonFiles($files,$source_id) {
        // create array
        $duplicates = [];
        // loop through files array
        for ($i=0; $i < count($files); $i++) { 
            $this->builder = $this->db->table($this->table);
            $this->builder->where('source_id', $source_id);
            $this->builder->where('FileName', $files[$i]);
            $count = $this->builder->countAllResults();

            // if the count is greater than 1 push it into duplicates array
            if ($count != 0) {
                array_push($duplicates, $files[$i]);
            }
        }
        return $duplicates;
    }

    /**
     * Is Duplicate File - Check if the given file/source combo already exists
     * 
     *
     * @param string $file - The File name we are checking
     * @param int $source  - The source Id we are checking
     * @return int 0 if new| 1 if duplicate
     */
    public function isDuplicateFile($file, $source_id) {
        $this->builder = $this->db->table($this->table);

        $this->builder->where('source_id', $source_id);
        $this->builder->where('FileName', $file);
        $query = $this->builder->countAllResults(); 
        return $query;
        // Takes a filename and source id argument
        // if returned value is 0 then it is new
        // if returned value is 1 then it is duplicate
    }

    /**
     * Pheno Packet Files - Get a list of all rows which are pending, have a .json extension  
     * for a given source. Used in PhenoPacket upload/insert
     *
     * @param int $source_id - The source_id we are checking
     * @return array empty if no files | with elements of file names
     */
    public function phenoPacketFiles(int $source_id) {

        $this->builder = $this->db->table($this->table);

        $this->builder->select('FileName, ID');
        $this->builder->where('source_id', $source_id);
        $this->builder->where('Status', 'Pending');
        $this->builder->like('FileName', '.phenopacket');
        $query = $this->builder->get()->getResultArray();

        $this->builder->select('FileName, ID');
        $this->builder->like('FileName', '.json');
        $this->builder->where('source_id', $source_id);
        $this->builder->where('Status', 'Pending');
        $query2 = $this->builder->get()->getResultArray();
        $query = array_merge($query,$query2);
        return $query;
    }

    /**
     * Pheno Packet Clear - Delete any data for given source_id/file_name combo in eavs table
     * Aimed at PhenoPacket Data
     *
     * @param int $source_id - The source_id we want to delete from
     * @param string $file   - The File name we want to delte from
     * @return N/A
     */
    public function phenoPacketClear($source_id,$file){

        $this->builder = $this->db->table('eavs');

        $this->builder->where('source_id', $source_id);
        $this->builder->where('fileName', $file);
        $this->builder->delete();
    }

    /**
     * Clear Error For File - Remove any errors for a file which has been reuploaded so that
     * we produce a list which is relevant only for that upload
     *
     * @param int $file_id  - the file id we are seeing if there are errors for
     * @return N/A
     */
    public function clearErrorForFile($file_id) {

        $this->builder = $this->db->table('upload_error');

        $this->builder->where('error_id', $file_id);
        $this->builder->delete();
    }

    /**
     * Check Negated for HPO - For given list of HPO terms check if the group they belong to has a
     * negated = false flag. Then return only those
     *
     * @param array $hpo    - List of HPO terms to check
     * @param int $file_id  - the file id where these HPO terms have come from
     * @return array $final - List of HPO terms which have negated=0
     */
    public function checkNegatedForHPO($hpo,$file_id) {

        $this->builder = $this->db->table('eavs');
        $final =[];
        for ($i=0; $i < count($hpo); $i++) { 

            $this->builder->select('uid');
            $this->builder->where('value', $hpo[$i]);
            $this->builder->where('fileName', $file_id);
            $query = $this->builder->get()->getResultArray();
            $this->builder->select('value');
            $this->builder->where('uid', $query[0]['uid']);
            $this->builder->where('attribute', "negated");
            $query = $this->builder->get()->getResultArray();
            if ($query[0]['value'] == 0) {
                array_push($final, $hpo[$i]);
            }
        }
        return $final;
    }

    /**
     * Add Upload Job Record - Add a row into the upload job tracker
     *
     * @param string $source - The name of the file being uploaded
     * @param string $uid    - The linking id stored in front end
     * @param int $user_id   - The user id this job is linked to
     * @return N/A
     */
    public function addUploadJobRecord($source_id,$uid,$user_id) {
        $this->builder = $this->db->table('upload_jobs');

        $data = array(
            'user_id' => $user_id,
            'source_id' => $source_id,
            'linking_id' => $uid);
        $this->builder->insert($data);
    }

    /**
     * Remove Upload Job Record - Remove a row from the upload job tracker
     *
     * @param int $user_id   - The user_id for the job to be deleted
     * @param int $source_id - The source_id for the job to be deleted
     * @return N/A
     */
    public function removeUploadJobRecord($user_id,$source_id) {
        $this->builder = $this->db->table('upload_jobs');

        $this->builder->where('user_id', $user_id);
        $this->builder->where('source_id', $source_id);
        $this->builder->delete();
    }
    
    /**
     * Json Insert - Taking the data from recursivePacket we add to our MySQL transaction
     * Adding to eavs table
     *
     * Moved to upload model by Mehdi Mehtarizadeh (02/08/2019)
     * @param string $uid   - The md5 ID linking groups together
     * @param int $source   - The ID of the source we are linking this data to - sources
     * @param int $file     - The ID of the file we have generated this data from - UploadDataStatus
     * @param string $id    - The subject id of the current phenoPacket
     * @param string $key   - The attribute column for given datapoint
     * @param string $value - The value column for given datapoint
     * @param boolean $test - Optional parameter if you wish to error log the created data array
     * 						  Pass in true in this location
     * @return N/A
     */
    public function jsonInsert($uid,$source,$file,$id,$key,$value, $test=false) {

        $this->builder = $this->db->table('eavs');

        $data = array(
            'uid'        =>  $uid,
            'source_id'     => $source,
            'fileName'   => $file,
            'subject_id' => $id,
            'attribute'  => $key,
            'value'      => $value);
        if ($test) {
            error_log(print_r($data,1));
        }
        $this->builder->insert($data);
    }

        /**
     * Error Insert - During our upload we have encountered an error 
     * Adding to upload_error table
     *
     * Moved to upload model by Mehdi Mehtarizadeh (02/08/2019)
     * @param int $file_id      - The File ID which the error is associated with
     * @param int $source_id    - The Source ID which the error is associated with
     * @param string $message   - The message describing the error in question
     * @param int $error_code   - The ID of the error
     * @param boolean $test     - Optional parameter if you wish to error log the created data array
     * 						      Pass in true in this location
     * @param boolean $continue - Optional parameter if we are adding an error but not aborting rest of 
     *  						  upload
     * @return N/A
     */
    public function errorInsert($file_id, $source_id, $message, $error_code, $test=false,$continue=false) {
        $this->builder = $this->db->table('upload_error');

        $data = array(
            'error_id'   => $file_id,
            'source_id'  => $source_id,
            'message'    => $message,
            'error_code' => $error_code);
        if ($test) {
            error_log(print_r($data,1));
        }
        $this->builder->insert($data);
        if (!$continue) {
            $this->builder = $this->db->table($this->table);

            $data = array(
                'Status' => 'Failed',
                'elasticStatus' => 'Stale');
            $this->builder->where('ID', $file_id);
            $this->builder->update($data);
        }
    }

    /**
     * Moved to upload model by Mehdi Mehtarizadeh (02/08/2019)
     */
    public function insertStatistics($file, $source) {
        $this->builder = $this->db->table('eavs');

        $this->builder->select("attribute, value, count(*) AS count");
        $this->builder->where("source_id", $source);
        $this->builder->where("fileName",$file);
        $this->builder->groupBy(["attribute","value"]);

        $query = $this->builder->get()->getResultArray();
        //$query = $this->db->query('SELECT attribute, value, count(*) AS count FROM eavs WHERE source = "'.$source.'" AND fileName="'.$file.'" GROUP BY attribute,value');
        foreach ($query as $row) {
            $data[] = $row;
        }
        $data = json_decode(json_encode($data),true);
        // error_log(print_r($data));
        $currAtt = "";
        for ($i=0; $i <count($data) ; $i++) { 
            if ($data[$i]["attribute"] != $currAtt){
                $currAtt = $data[$i]["attribute"];
                $final[$data[$i]["attribute"]] = array();
                $final[$data[$i]["attribute"]][$data[$i]['value']] = $data[$i]['count'];
            }
            else {
                $final[$data[$i]["attribute"]][$data[$i]['value']] = $data[$i]['count'];
            }
        }
        $final = json_encode($final);

        $sourceModel = new Source($this->db);

        $fileName = $this->getFileName($file);
        //$sourceName = $sourceModel->getSourceNameByID($source);
        $file_name = preg_replace("/\.json|\.phenopacket|\.csv|\.xlsx|\.xls/", '', $fileName);
        $wr = write_file(getcwd() . "/upload/UploadData/".$source."/".$file_name."_uniq.json", $final );
        if ( ! $wr){
            var_dump( 'Unable to write the file'.$wr);
        }
    } 
        
    /**
     * Big Insert Wrap - Fill in the status table on the success of the upload
     *
     * Moved to upload model by Mehdi Mehtarizadeh (02/08/2019)
     * 
     * @param string $file   - The file we just finished uploading
     * @param int $source_id - The ID of the source we have uploaded to
     * @return N/A
     */
    public function bigInsertWrap($file, $source_id) {
        #This function updates the UploadDataStatus table once the sql insert is complete

        $this->builder = $this->db->table($this->table);
        $query = $this->builder->get()->getResultArray();
        #set sql entries
        $now = date('Y-m-d H:i:s');
        $uploadEnd = $now;
        $Status = "Success";
        $data = [
            'uploadEnd' => $now,
            'Status' => $Status,
            'elasticStatus' => 'Fresh'];
        $this->builder = $this->db->table($this->table);
        $this->builder->where('ID', $file);
        $this->builder->update($data);

        $this->builder = $this->db->table('sources');

        $data = [
            'elastic_status' => 1];
        $this->builder->where('source_id', $source_id);
        $this->builder->update($data);

    }

    /**
     * Patient Subject Source Combo - Does this combo of Source/Patient/Tissue already exist?
     *
     * @param int $source_id  - The source_id we are checking
     * @param string $patient - The Patient we are checking
     * @param string $tissue  - The tissue we are checking  
     * @return int 0 if doesnt exist| 1 if it does
     */
    public function patientSubjectSourceCombo($source_id,$patient,$tissue) {
        $this->builder = $this->db->table($this->table);

        $this->builder->where('source_id', $source_id);
        $this->builder->where('patient', $patient);
        $this->builder->where('tissue', $tissue);
        $query = $this->builder->countAllResults(); 
        return $query;
    }

    /**
     * VCF Start -  Perform Initial insert into vcf_elastic table
     * to keep track of uploaded file
     * @deprecated
     * @param string $file    - The File name we have uploaded
     * @param string $source  - The source name we are adding to
     * @param string $tissue  - The Tissue this VCF data is sampled from
     * @param string $patient - The Subject ID this VCF data is sample from
     * @return N/A
     */
    public function vcfStart(string $file, int $source_id, int $user_id, $tissue, $patient) {

        error_log("file: ".$file." source: ".$source_id. " tissue: ".$tissue." patient: ".$patient);

        $this->builder = $this->db->table($this->table);

        // Set current time
        $now = date('Y-m-d H:i:s');

        // Check if this VCF is duplicated
        if ($this->isDuplicateVcf($file,$source_id)) {
            // If it has all we need to do is to update a current row with some of the 
            // information which is current
            $data = array(
                'user_id' => $user_id,
                'uploadstart' => $now,
                'uploadend' => null,
                'Status' => 'Pending');
            $this->builder->where('source_id', $source_id);
            $this->builder->where('filename', $file);
            $this->builder->update($data);
        }
        else {
            // We havent seen this file/source combination before. Add whole row
            $data = array(
                'source_id' => $source_id,
                'user_id' => $user_id,
                'filename' => $file,
                'uploadstart' => $now,
                'uploadend' => null,
                'status' => 'Pending',
                'patient' => $patient,
                'tissue' => $tissue);				
            $this->builder->insert($data);
        }		
    }

    /**
     * Is Duplicate VCF - Has a VCF with same name and source been uploaded before?
     *
     * @param string $file   - The file name we are checking
     * @param int $source_id - The source_id we are checking
     * @return int 0 if doesnt exist| 1 if it does
     */
    public function isDuplicateVcf($file,$source_id) {

        $this->builder = $this->db->table($this->table);

        $this->builder->select('*');
        $this->builder->where('source_id', $source_id);
        $this->builder->where('filename', $file);
        $query = $this->builder->countAllResults(); 
        return $query;
    }

    /**
     * isDuplicatePhysicalFile - Perform checks on the file from do_upload to see if the file can be uploaded
     * Checks if the directory to upload to exists
     *
     * Moved by Mehdi Mehtarizadeh 07/08/2019
     * 
     * @param string $source_id    - The source id we will be uploading to
     * @param string $file_name - The file we are uploading
     * @param string $tmp       - The file path for where the file is stored in /tmp
     *							  prior to being uploaded
     * @return bool 
    */
    public function isDuplicatePhysicalFile($source_id,$file_name, $tmp): bool{
        $source_path = FCPATH."upload/UploadData/".$source_id;
        if (!file_exists($source_path)) {
            mkdir($source_path);
        }
        $file_path = FCPATH."upload/UploadData/".$source_id."/".$file_name;
        if (file_exists($file_path)){
           return true;
        }
        else {
            return false;
        }    	
    }

    
 }
 