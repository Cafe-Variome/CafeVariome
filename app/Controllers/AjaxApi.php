<?php namespace App\Controllers;

/**
 * AjaxApi.php 
 * 
 * Created 15/08/2019
 * 
 * @author Mehdi Mehtraizadeh
 * @author Gregory Warren
 * @author Owen Lancaster
 * @author Farid Yavari Dizjikan
 * 
 * This controller contains listener methods for client-side ajax requests.
 * Methods in this controller were formerly in other controllers. 
 * Code must be more secure. Some of the methods here must be moved to back-end layers for security reasons.
 */

use CodeIgniter\Controller;
use App\Helpers\AuthHelper;
use App\Models\Settings;
use App\Libraries\CafeVariome\Net\NetworkInterface;
use App\Libraries\CafeVariome\Net\QueryNetworkInterface;
use App\Libraries\CafeVariome\Net\HPONetworkInterface;
use App\Models\EAV;
use App\Models\Source;
use App\Models\Network;
use App\Models\Elastic;
use App\Models\Upload;
use App\Libraries\CafeVariome\Core\IO\FileSystem\UploadFileMan;
use App\Libraries\CafeVariome\Core\IO\FileSystem\SysFileMan;
use App\Libraries\CafeVariome\PHPShellHelper;
use App\Libraries\CafeVariome\Auth\AuthAdapter;
use CodeIgniter\Config\Services;

 class AjaxApi extends Controller{

	protected $db;

    protected $setting;
    
    private $phpshellHelperInstance;

    /**
	 * Constructor
	 *
	 */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger){
		parent::initController($request, $response, $logger);
		$this->db = \Config\Database::connect();

        $this->setting =  Settings::getInstance();

        $this->sourceModel = new Source($this->db);
        $this->uploadModel = new \App\Models\Upload($this->db);

        $this->phpshellHelperInstance = new PHPShellHelper();
        
    }

    function query($network_key = '') {
        $networkInterface = new NetworkInterface();
        
		$authAdapterConfig = config('AuthAdapter');
        $authAdapter = new AuthAdapter($authAdapterConfig->authRoutine);
        
        //Check to see if user is logged in
        if (!$authAdapter->loggedIn()) {
            return json_encode(['timeout' => 'Your session has timed out. You need to login again.']);
        }

        $queryString = json_encode($this->request->getVar('jsonAPI'));
        $token = $authAdapter->getToken();

        $user_id = $authAdapter->getUserIdByToken($token);

        try {
            $results = [];
            $cafeVariomeQuery = new \App\Libraries\CafeVariome\Query();
            $loaclResults = $cafeVariomeQuery->search($queryString, $network_key, $user_id); // Execute locally
            array_push($results, $loaclResults);
    
            $response = $networkInterface->GetInstallationsByNetworkKey((int)$network_key); // Get other installations within this network
            $installations = [];
    
            if ($response->status) {
                $installations = $response->data;
    
                foreach ($installations as $installation) {
                    if ($installation->installation_key != $this->setting->getInstallationKey()) {
                        // Send the query
                        $queryNetInterface = new QueryNetworkInterface($installation->base_url);
                        $queryResponse = $queryNetInterface->query($queryString, (int) $network_key, $token);
                        if ($queryResponse->status) {
                            array_push($results, json_encode($queryResponse->data));
                        }
                    }
                }
            }
    
            return json_encode($results);
        } catch (\Exception $ex) {
            return json_encode(['error' => 'There was a problem executing the query. Please try again with a different query.']);
        }
    }

    function HPOQuery(string $hpo_term = ''){

        $hpoNetworkInterface = new HPONetworkInterface();
        $results = $hpoNetworkInterface->getHPO($hpo_term);
        return json_encode($results);
    }

    function build_tree() {
        if ($this->request->isAJAX())
        {
            $hpoNetworkInterface = new HPONetworkInterface();

            $hpo_json = json_decode(stripslashes($this->request->getVar('hpo_json')), 1);
            $hpo_json = json_decode(str_replace('"true"', 'true', json_encode($hpo_json)), 1);
            $hpo_json = json_decode(str_replace('"false"', 'false', json_encode($hpo_json)), 1);
        
            $ancestry = $this->request->getVar('ancestry');
            $hp_term = explode(' ', $this->request->getVar('hp_term'))[0];
        
            $splits = explode('||', $ancestry);
            foreach ($splits as $split) {
                $parent = &$hpo_json;
    
                $ancestor = explode('|', $split);
                $str = 'HP:0000001';
                foreach(array_reverse($ancestor) as $term) {
                    if($term === 'HP:0000001') continue;
    
                    $str .= ".$term";
                    if(array_key_exists('children', $parent) && is_array($parent['children'])) {
                        foreach($parent['children'] as &$child) {
                            if($child['id'] === $str) {
                                $parent = &$child;
                                if(array_key_exists('children', $parent) && is_array($child['children'])) {
                                    $parent['children'] = &$child['children'];
                                } else {
                                    $parent['state']['opened'] = "true";
                                    unset($parent['state']['loading']);
                                    unset($parent['state']['loaded']);
                                    $dat = json_encode($hpoNetworkInterface->getHPO($str));
                                    $parent['children'] = json_decode($dat, 1);
                                }
                                break;
                            }
                        }
                    }
                }
                if(array_key_exists('children', $parent)) {
                    foreach($parent['children'] as &$child) {
                        if($child['id'] === $str . ".$hp_term") {
                            $child['state']['selected'] = "true";
                        }
                    }
                }
            }
            $hpo_json = str_replace('"true"', 'true', $hpo_json);
            $hpo_json = str_replace('"false"', 'false', $hpo_json);
    
            return json_encode($hpo_json);
        }
	}

	/**
     * getPhenotypeAttributes
     * @param string network_key 
     * @return string in json format, phenotype and hpo data
     * 
     */
    function getPhenotypeAttributes(string $network_key) {
        if ($this->request->isAJAX())
        {
            $basePath = FCPATH . JSON_DATA_DIR;

            $fileMan = new SysFileMan($basePath);
            $networkInterface = new NetworkInterface();
            $response = $networkInterface->GetInstallationsByNetworkKey((int)$network_key);

            $installations = [];

            if ($response->status) {
                $installations = $response->data;
            }
    
            $data = array();
    
            foreach ($installations as $installation) {
                $queryNetInterface = new QueryNetworkInterface($installation->base_url);
                $eavJson = $queryNetInterface->getEAVJSON($network_key, $fileMan->GetModificationTimeStamp("local_" . $network_key . ".json"));
                $result = $eavJson->data->json;

                if ($result) {
                    foreach (json_decode($result, 1) as $res) {
    
                        if (array_key_exists($res['attribute'], $data)) {
                            foreach (explode("|", strtolower($res['value'])) as $val) {
                                if (!in_array($val, $data[$res['attribute']]))
                                    array_push($data[$res['attribute']], $val);
                            }
                        }
                        else {
                            $data[$res['attribute']] = explode("|", strtolower($res['value']));
                        }
                    }
                }
            }
    
            foreach(array_keys($data) as $key){
                sort($data[$key]);
            }
            
            ksort($data);
    
            if ($data) {
                $fileMan->Write("local_" . $network_key . ".json", json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE));
            }
    
            $hpoDataString = '';
            foreach ($installations as $installation) {
                $queryNetInterface = new QueryNetworkInterface($installation->base_url);
                $hpoJson = $queryNetInterface->getHPOJSON($network_key, $fileMan->GetModificationTimeStamp("local_" . $network_key . "_hpo_ancestry.json"));
                $hpoDataString = $hpoJson->data->json;

                if ($hpoDataString) {
                    $fileMan->Write("local_" . $network_key . "_hpo_ancestry.json", json_encode($hpoDataString));
                }
            }
            
            $phen_data = [];
            $hpo_data = [];

            if ($fileMan->Exists("local_" . $network_key . ".json")) {
                $phen_data = json_decode($fileMan->Read("local_" . $network_key . ".json"), 1);
            }

            if ($fileMan->Exists("local_" . $network_key . "_hpo_ancestry.json")) {
                $hpo_data = json_decode($fileMan->Read("local_" . $network_key . "_hpo_ancestry.json"), 1);
            }
            
            return json_encode([$phen_data, $hpo_data]);
        }
    }

    /**
     * validateUpload - Ensure the source we are wanting to upload to is an actual source
     * Users can change the parameter on url to what they wish
     * Check if the source is locked by another update/upload operation
     * Perform check that there is enough space on the webserver to upload given file/files
     * Echo result to js front end to determine response to user
     * @param string $_POST['source'] - The source name we will be uploading to and checking against
     * @param int $_POST['size']      - The size in bytes of file/files to be uploaded
     * @return string Green(Success)|Yellow(Not enough space on server)|Red(Source is locked) Red(Source doesnt exist)
    */

    public function validateUpload() {
        // Source we are checking against
        $source_id = $this->request->getVar('source_id');
        $space_needed = $this->request->getVar('size');
        // check if it exists
        
        $sourceExists = $this->sourceModel->getSource($source_id);
        if ($sourceExists) {
            // Since it exists get its source id and then check if its locked
            $isLocked = $this->sourceModel->isSourceLocked($source_id);
            if (!$isLocked) {					
                // if its not locked check if we have enough space on the server
                $free = diskfreespace(FCPATH);
                if ($space_needed > $free) {
                    // There is not enough space on server
                    return json_encode("Yellow");
                }
                else {
                    // All checks passed
                    return json_encode("Green");
                }
            }
            else {
                // The source is locked
                return json_encode("Locked");
            }
        }
        else {
            // The source target doesnt exist
            return json_encode("Red");
        }
    }

    /**
     * Check Json Presence - Check if the server has any of the targeted json files 
     * Already present for this source
     *
     * @param array $_POST['files']  - The list of files we must check presence for
     * @param string $_POST['source'] - The source we must check presense for inside
     * @return string Green | json_encoded array with list of files
     */
    public function checkJsonPresence() {

        $duplicates = [];

        $source_id = $this->request->getVar('source_id');
        $fileNames = $this->request->getVar('fileNames');

        $sourceFiles = $this->uploadModel->getFiles('FileName', ['source_id' => $source_id]);

        foreach ($sourceFiles as $sourceFile) {
            if (in_array($sourceFile['FileName'], $fileNames)) {
                array_push($duplicates, $sourceFile['FileName']);
            }
        }

        if (count($duplicates) > 0) {
            return json_encode($duplicates);
        }
        else {
            return json_encode("Green");
        }
    }

    /**
     * Json Batch - At this point all checks have been performed. Upload the json files in
     * Batches of 20 (as limited by php.ini)
     *
     * @param array $_FILES          - The list of files we must upload
     * @param string $_POST['source'] - The source we must upload into
     * @return N/A
     */
    public function jsonBatch() {
        
        $source_id = $this->request->getVar('source_id');
        $user_id = $this->request->getVar('user_id');
        $pipeline_id = $this->request->getVar('pipeline_id');

        $basePath = FCPATH . UPLOAD . UPLOAD_DATA;
        // Create the source upload directory if it doesnt exist
        $source_path =  $source_id;

        $fileMan = new UploadFileMan($basePath);
        if (!$fileMan->Exists($source_path)) {
            $fileMan->CreateDirectory($source_path);
        }

        $source_path =  $source_id . DIRECTORY_SEPARATOR;

        if (!$fileMan->Exists($source_path)) {
            $fileMan->CreateDirectory($source_path);
        }

        $files = $fileMan->getFiles();

        foreach ($files as $file) {    
            if (!$fileMan->isValid($file)) {
                return false;
            }   

            if($fileMan->Save($file, $source_path))
            {     
                $this->uploadModel->createUpload($file->getName(),$source_id, $user_id, false, false, null, $pipeline_id);
            }
            else
            {
                return false;
            } 
        }
        
        return true;
    }
 
    /**
     * Json Start - At this point all files have been uploaded. Lock the source and begin 
     * Insert into MySQL
     *
     * @param string $_POST['source'] - The source we must upload into
     * @return string Green for success
     */
    public function jsonStart() {
        // Assign posted source to easier variable
        $source_id = $this->request->getVar('source_id');
        $user_id = $this->request->getVar('user_id');
        // Get ID for source and lock it so further updates and uploads cannot occur
        // Until update is finished
        $this->sourceModel->lockSource($source_id);
        $uid = md5(uniqid(rand(),true));
        $this->uploadModel->addUploadJobRecord($source_id,$uid,$user_id);

        // Create thread to begin SQL insert in the background
        $this->phpshellHelperInstance->runAsync(getcwd() . "/index.php Task phenoPacketInsertBySourceId " . $source_id . " 00");

        // Report to front end that the process has now begun
        echo json_encode("Green");
    }

    public function checkUploadJobs() {

        $user_id = $this->request->getVar('user_id');
        $return = ['Status' => '', 'Message' => []];
        if ($this->uploadModel->countUploadJobRecord($user_id)) {
            $return['Status'] = true;
            $values = $this->uploadModel->checkUploadJobRecord($user_id);
            for ($i=0; $i < count($values); $i++) { 
                if ($values[$i]['elastic_lock'] == 0) {
                    $source_name = $this->uploadModel->getSourceNameByID($values[$i]['source_id']);
                    array_push($return['Message'], $source_name);
                    $this->uploadModel->removeUploadJobRecord($user_id,$values[$i]['source_id']);
                }
            }
        }
        else {
            $return['Status'] = false;
        }
        echo json_encode($return);
    }
    
    public function vcf_upload() {

        $basePath = FCPATH . UPLOAD . UPLOAD_DATA;
        $pairingsPath = FCPATH . UPLOAD . UPLOAD_PAIRINGS;

        $fileMan = new UploadFileMan($basePath);

        $response_array = array('status' => "",'message' => []);

        if ($fileMan->countFiles() == 1){ 
            // Only one config file is allowed to be uploaded at the moment.
            $configFile = $fileMan->getFiles()[0];
            $configFileName = $configFile->getName();
            $configFileExtension = $configFile->getExtension();
            $configFileTempPath = $configFile->getTempPath();
        }
        else {
            $response_array['status'] = "Cancel";
            $response_array['message'] = "Config file is either not uploaded or is missing.";

            return json_encode($response_array);
        }

        $source_id = $this->request->getVar('source_id');
        $fileNames = $this->request->getVar('files'); // Name of VCF files that will be uploaded pending they meet conditions.
        $fileNamesArray = explode(",", $fileNames); // Array of the file names above

        $spreadsheet =  \PhpOffice\PhpSpreadsheet\IOFactory::load($configFileTempPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow(); 
        $highestColumn = $worksheet->getHighestColumn(); 
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        $headers = [];
        $dup_files = [];
        $dup_elastic = [];
        $pairings = [];
        $types = [];

        array_push($headers, "");

        $filesCount = count($fileNamesArray);

        if ($filesCount > 200) {
            error_log("overload");
            $response_array['status'] = "Overload";
            $response_array['message'] = "You are trying to upload more than 200 files. Please limit your upload to 200 files or less.";

            return json_encode($response_array);
        }

        if ($configFileExtension == "csv" || $configFileExtension == "xls") {
            for ($row = 1; $row <= $highestRow; ++$row) {
                for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                    
                    if ($row == 1) {
                        $value = $worksheet->getCellByColumnAndRow($col, $row)->getValue();

                        if (!preg_match("/filename|patient|tissue/", strtolower($value))) {
                            $message = "Headers in " . $configFileName . " not in FileName,Patient,Tissue format.";
                            array_push($response_array['message'], $message);

                            return json_encode($response_array);
                        }
                        else {	    					
                            array_push($headers, strtolower($value));
                        }
                    }
                    else {
                        $value = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                        $key = $headers[$col];
                        switch ($key) {
                            case 'filename' :
                                $flag = 0;						      
                                if (is_array($fileNamesArray)) {
                                    if (in_array($value, $fileNamesArray)) $flag = 1;
                                }
                                else {
                                    if ($value == $fileNamesArray) $flag = 1;
                                }	
                                if (!$flag) {
                                    $message = "File: ".$value." not found in list of Uploaded Files from config file: ". $configFileName;
                                    array_push($response_array['message'], $message);			
                                }
                                if (!preg_match("/\.vcf$|\.vcf\.gz$/", $value)) {
                                    $message = "File: ".$value." is not a vcf file.";
                                    array_push($response_array['message'], $message);
                                }

                                $file_path = $source_id . DIRECTORY_SEPARATOR . $value;
                                if ($fileMan->Exists($file_path)) {
                                    array_push($dup_files, $value);
                                }
                                $file = $value;
                                break ;	
                            case 'tissue' :
                                $tissue = $value;
                                break;
                            case 'patient' :
                                $patient = $value;
                                break;
                        }				    					
                    }
                }
                if ($row == 1) {
                    continue;
                }
                if ($this->uploadModel->patientSubjectSourceCombo($source_id,$patient,$tissue)) {
                    // if the file already exists and we get true from prior if
                    // the file is duplicated and the patient/source/tissue exists
                    array_push($dup_elastic, $file);
                }	
                $pairings[$file][] = $tissue;
                $pairings[$file][] = $patient;
            }
            if (!empty($response_array['message'])) {
                $response_array['status'] = "Cancel";
                if (!empty($dup_files)) {
                    $response_array['files'] = $dup_files;
                }
                if (!empty($dup_elastic)) {
                    $response_array['elastic'] = $dup_elastic;
                }
            }	
            else if (empty($dup_files) && empty($dup_elastic)) {
                $response_array['status'] = "Green";
                $response_array['message'] = "no errors";
            }
            else if (!empty($dup_files) && !empty($dup_elastic)) {
                $response_array['status'] = "Duplicate";
                $both = array_intersect($dup_files, $dup_elastic);
                if ($both) {
                    $dup_files = array_diff($dup_files, $both);
                    $dup_elastic = array_diff($dup_elastic, $both);
                    $response_array['both'] = $both;
                    array_push($types, "both");
                    if ($dup_files) {
                        $dup_files = array_values($dup_files);
                        $response_array['files'] = $dup_files;
                        array_push($types, "files");
                    }
                    if ($dup_elastic) {
                        $dup_elastic = array_values($dup_elastic);
                        $response_array['elastic'] = $dup_elastic;
                        array_push($types, "elastic");
                    }		         		
                } 
                else {
                    $response_array['elastic'] = $dup_elastic;
                    $response_array['files'] = $dup_files;
                    array_push($types, "elastic");
                    array_push($types, "files");
                }		
            }
            else {
                $response_array['status'] = "Duplicate";
                if (!empty($dup_files)) {
                    $response_array['files'] = $dup_files;
                    array_push($types, "files");
                }
                if (!empty($dup_elastic)) {
                    $response_array['elastic'] = $dup_elastic;
                    array_push($types, "elastic");
                }
            }        	
        }
        else {
            $response_array['status'] = "Cancel";
            array_push($response_array['message'], "Config file is not in correct format. Cannot be read.");

            return json_encode($response_array);		
        }

        if (!$fileMan->Exists($source_id))
        {
            $fileMan->CreateDirectory($source_id);
        }

        $fileMan = new UploadFileMan($pairingsPath);
        
        $uid = md5(uniqid(rand(),true));  

        $fileMan->Write($uid.".json", json_encode($pairings));

        $response_array['uid'] = $uid;
        $response_array['types'] = $types;
        
        return json_encode($response_array);
    }

    public function vcfBatch() {

        $basePath = FCPATH . UPLOAD;
        $fileMan = new UploadFileMan($basePath);

        $source_id = $this->request->getVar('source_id'); 
        $uid = $this->request->getVar('uid');
        $user_id = $this->request->getVar('user_id');

        if ($fileMan->Exists(UPLOAD_PAIRINGS . $uid . ".json")) {
            $pairings = json_decode($fileMan->Read(UPLOAD_PAIRINGS . $uid . ".json"), true);

            $source_path = UPLOAD_DATA . $source_id . DIRECTORY_SEPARATOR;	
    
            // Check the number of files we are uploading
            $filesCount = $fileMan->countFiles();
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
    
            $userFiles = $fileMan->getFiles();
            
            for($i = 0; $i < $filesCount; $i++){     
                // Check the mime and extension for the file we are currently uploading
                $fileName = $userFiles[$i]->getName();
                $mime = $userFiles[$i]->getType();
                $extension = $userFiles[$i]->getExtension();

                if ($mime != "text/vcard" &&  $xtension == "json") {
                    error_log("failure");
                }   
    
                if($fileMan->Save($userFiles[$i], $source_path))
                {   
                    // 13/08/2019 POTENTIAL BUG 
                    // The value for patient must be specified as it is always set to 0 (false)
                    $this->uploadModel->createUpload($fileName, $source_id, $user_id, $pairings[$fileName][0],$pairings[$fileName][1]);
                }
                else {
                    // if it failed to upload report error
                    // TODO: Make it return failure and reflect in JS for this eventuality
                }
            }

            return json_encode("Green");
        }
    }

    public function vcfStart() {

        $pairingsPath = FCPATH . UPLOAD . UPLOAD_PAIRINGS;
        $fileMan = new UploadFileMan($pairingsPath);

        $source_id = $this->request->getVar('source_id');
        $user_id = $this->request->getVar('user_id');
        $uid = $this->request->getVar('uid');
        $overwrite = $this->request->getVar('fAction');
        
        // Get ID for source and lock it so further updates and uploads cannot occur until update is finished
        $this->sourceModel->lockSource($source_id);
        $this->uploadModel->addUploadJobRecord($source_id, $uid, $user_id);

        $path = $uid.".json";

        if($fileMan->Exists($path)) {
            $fileMan->Delete($path);
        }

        if ($overwrite == "overwrite") {
            $this->phpshellHelperInstance->runAsync(getcwd() . "/index.php Task vcfInsertBySourceId " . $source_id . " " . UPLOADER_DELETE_ALL);
        }
        elseif ($overwrite == "append") {
            $this->phpshellHelperInstance->runAsync(getcwd() . "/index.php Task vcfInsertBySourceId " . $source_id . " " . UPLOADER_DELETE_NONE);
        }

        return json_encode("Green");
    }

    /**
     * bulk_upload - Perform Upload for CSV/XLS/XLSX files
     * TODO: Create a link to page from sources admin. Replace with stand alone vcf page
     *
     * @param string $_POST['source'] - The source name we will be uploading to
     * @param array $_FILES           - The file we are uploading
     * @return json_encoded array Success|Headers are not as expected|File is Duplicated
     */
    public function bulk_upload($force=false){   

        $source_id = $this->request->getVar('source_id');
        $user_id = $this->request->getVar('user_id');
        $pipeline_id = $this->request->getVar('pipeline_id');
        
        $basePath = FCPATH . UPLOAD . UPLOAD_DATA;
        $fileMan = new UploadFileMan($basePath);

        if ($fileMan->countFiles() == 1){ // Only 1 file is allowed to go through this uploader
            $file = $fileMan->getFiles()[0];
            $file_name = $file->getName();

            if (!$force) {
                if($fileMan->Exists($source_id . DIRECTORY_SEPARATOR . $file_name)){
                    $response_array = array('status' => "Duplicate");
                    return json_encode($response_array);
                }
            }

            if (!$fileMan->isValid($file)) {
                $response_array = array('status' => "InvalidFile");
                return json_encode($response_array);
            }

            $source_path = $source_id . DIRECTORY_SEPARATOR;
            if (!$fileMan->Exists($source_id)) {
                $fileMan->CreateDirectory($source_id);
            }
            if ($fileMan->Save($file, $source_path)) {
                
                $file_id = $this->uploadModel->createUpload($file_name, $source_id, $user_id, false, false, null, $pipeline_id);

                // Begin background insert to MySQL

                $fAction = $this->request->getVar('fAction'); // File Action 
                if ($fAction == "overwrite") {
                    $this->phpshellHelperInstance->runAsync(getcwd() . "/index.php Task bulkUploadInsert $file_id 1 $source_id");
                }
                elseif ($fAction == "append") {
                    $this->phpshellHelperInstance->runAsync(getcwd() . "/index.php Task bulkUploadInsert $file_id 00 $source_id");
                }
                $uid = md5(uniqid(rand(),true));
                $this->uploadModel->addUploadJobRecord($source_id,$uid,$user_id);
                $response_array = array('status'  => "Green",
                                        'message' => "",
                                        'uid'     => $uid);
                return json_encode($response_array);
            }
            else{
                $response_array = array('status'  => "Red",
                'message' => "Unknown error.");

                return json_encode($response_array);
            }
        }
    }

    /**
     * univ_upload - Perform Upload for CSV/XLS/XLSX files
     *
     * @param string $_POST['source'] - The source name we will be uploading to
     * @param string $_POST['config'] - The settings file uesd for import
     * @param array $_FILES           - The file we are uploading
     * @return json_encoded array Success|Headers are not as expected|File is Duplicated
     */
    public function univ_upload($force=false){   

        $source_id = $this->request->getVar('source_id');
        $user_id = $this->request->getVar('user_id');
        $setting_file = $this->request->getVar('config');

        $basePath = FCPATH . UPLOAD . UPLOAD_DATA;
        $fileMan = new UploadFileMan($basePath);

        if ($fileMan->countFiles() == 1){ // Only 1 file is allowed to go through this uploader
            $file = $fileMan->getFiles()[0];
            $tmp = $file->getTempPath();
            $file_name = $file->getName();
            if (!$force) {
                if($fileMan->Exists($source_id . DIRECTORY_SEPARATOR . $file_name)){
                    $response_array = array('status' => "Duplicate");
                    echo json_encode($response_array);
                    return;
                }
            }

            $source_path = $source_id . DIRECTORY_SEPARATOR;
            if (!$fileMan->Exists($source_id)) {
                $fileMan->CreateDirectory($source_id);
            }
            if ($fileMan->Save($file, $source_path)) {

                $file_id = $this->uploadModel->createUpload($file_name, $source_id, $user_id, false, false, $setting_file);

                // Begin background insert to MySQL

                $fAction = $this->request->getVar('fAction'); // File Action 
                if ($fAction == "overwrite") {
                    $this->phpshellHelperInstance->runAsync(getcwd() . "/index.php Task univUploadInsert $file_id 1 $source_id $setting_file");
                }
                elseif ($fAction == "append") {
                    $this->phpshellHelperInstance->runAsync(getcwd() . "/index.php Task univUploadInsert $file_id 00 $source_id $setting_file");
                }
                else {
                    error_log("entered else");
                    return;
                }	
                $uid = md5(uniqid(rand(),true));
                $this->uploadModel->addUploadJobRecord($source_id,$uid,$user_id);
                $response_array = array('status'  => "Green",
                                        'message' => "",
                                        'uid'     => $uid);
                echo json_encode($response_array);
            }
            else{
                #shouldnt ever happen
                error_log("entered else");
            }
        }
    }

    public function processFile()
    {
        $fileId = $this->request->getVar('fileId');
        $overwrite = $this->request->getVar('overwrite');

        $uploadModel = new Upload();
        $extension = $uploadModel->getFileExtensionById($fileId);

        $method = '';
        $overwriteFlag = UPLOADER_DELETE_FILE;
        
        switch (strtolower($extension)) {
            case 'csv':
            case 'xls':
            case 'xlsx':
                $method = 'bulkUploadInsert';
                $overwriteFlag = $overwrite ? UPLOADER_DELETE_ALL : UPLOADER_DELETE_NONE;
                break;
            case 'phenopacket':
                $method = 'phenoPacketInsertByFileId';
                $overwriteFlag = UPLOADER_DELETE_FILE;
                break;
            case 'vcf':
                $method = 'vcfInsertByFileId';
                $overwriteFlag = UPLOADER_DELETE_FILE;
                break;
            default:
                return json_encode(0);
                break;
        }

        $uploadModel = new Upload();
        $uploadModel->resetFileStatus($fileId);

        $this->phpshellHelperInstance->runAsync(getcwd() . "/index.php Task " . $method . " " . $fileId . " " . $overwriteFlag);

        return json_encode(1);
    }

    public function getSourceCounts()
    {
        $sourceModel = new Source();
        $sourceRecordount = $sourceModel->getSources('source_id, name, record_count', ['status' => 'online']);

        $sc = 0;
        $maxSourcesToDisplay = 12;
        $sourceCountList = [];
        foreach ($sourceRecordount as $srCount) {
            if ($sc > $maxSourcesToDisplay) {
                break;
            }
            array_push($sourceCountList, $srCount['record_count']);
            $sc++;
        }

        return json_encode($sourceCountList);
    }

    public function getSourceStatus(int $source_id){
        $sourceModel = new Source();
        $output = ['Files' => [], 'Error' => []];
        if ($source_id== 'all') {
            $output['Files'] = $sourceModel->getSourceStatus('all');
        }
        else {
          $output['Files'] = $sourceModel->getSourceStatus($source_id);
          $output['Error'] = $sourceModel->getErrorForSource($source_id);
        }       
        
        return json_encode($output);   
    }


    /**
     * Elastic Check - Checking function prior to update to determine type of update desired and whether it is needed.
     *
     * @param int $force     - Are we forcing the regnerate? 1 if so and 0 if not
     * @param int $id        - The source id for the elasticsearch index
     * @param int $add       - 1 if we are adding to index instead of fully regenerating
     * @return array $result - Various parameters to allow front end decision
     */
    public function elastic_check() {	   
            
        $uploadModel = new \App\Models\Upload(); 
        $eavModel = new EAV();

        $data = json_decode($this->request->getVar('u_data'));
        $force = $data->force;
        $source_id = $data->id;
        $add = $data->add;

        $unprocessedFilesCount = $uploadModel->getElasticsearchUnprocessedFilesBySourceId($source_id);

        if (!$unprocessedFilesCount) {
            $result = ['Status' => 'Empty'];
            return json_encode($result);
        }
        if ($add) {
            $unaddedEAVsCount = $eavModel->countUnaddedEAVs($source_id);
            if ($unaddedEAVsCount == 0) {
                $result = ['Status' => 'Fully Updated'];
                return json_encode($result);
            }
            else {
                $time = $unaddedEAVsCount/2786;
                $result = ['Status' => 'Success','Time'=> $time];
                return json_encode($result);
            }
        }
        else {
            if ($force) {
                $count = $eavModel->countUnaddedEAVs($source_id);
                $time = $count/2786;
                $result = ['Status' => 'Success','Time'=> $time];
                return json_encode($result);
            }
            else {
                $result = ['Status' => 'Fully Updated'];
                return json_encode($result);
            }
        } 	
    }


    /**
     * Elastic Start - Begin ElasticSearch regeneration
     *
     * @param int $force     - Are we forcing the regnerate? 1 if so and 0 if not
     * @param int $id        - The source id for the elasticsearch index
     * @param int $add       - 1 if we are adding to index instead of fully regenerating
     * @return N/A
     */
    public function elastic_start() {
        $eavModel = new \App\Models\EAV(); 
        $phpshellHelperInstance = new PHPShellHelper();
        $data = json_decode($this->request->getVar('u_data'));
        $force = $data->force;
        $source_id = $data->id;
        $add = (int)$data->add;

        if ($force) {
            // if the regenerate was forced set the elastic state for all eav data rows
            $eavModel->resetElasticFlag($source_id);
        }
        
        // rebuild the json list for interface
        $phpshellHelperInstance->runAsync(getcwd() . "/index.php Task regenerateElasticsearchAndNeo4JIndex $source_id $add");
    }

    function loadOrpha(){
        $this->response->setHeader("Content-Type", "application/json");

        $path = FCPATH . RESOURCES_DIR . STATIC_DIR;
        $fileMan = new SysFileMan($path);
        $terms = [];
        if ($fileMan->Exists(ORPHATERMS_SOURCE)) {
            while (($line = $fileMan->ReadLine(ORPHATERMS_SOURCE)) != false) {
                $termPair = $line;
                $termPair = str_replace('"', "", $termPair);
                $termPair = str_replace("\n", "", $termPair);
                $termPairArr = explode(",", $termPair);

                array_push($terms, $termPairArr[0] . ' ' . $termPairArr[1]); 
            }
        }
        
		return json_encode($terms);
	}

    public function getAttributeValueFromFile()
    {
        if ($this->request->isAJAX()) {       
            $source_id = $this->request->getVar('source_id');

            $path = FCPATH . UPLOAD . UPLOAD_DATA . $source_id . DIRECTORY_SEPARATOR;
            $fileMan = new SysFileMan($path);

            $attributeValueData = [];
            foreach ($fileMan->getFiles() as $file) {
                if (strpos($file, '_uniq.json')) {
                    $attributeValueData[$file] = $fileMan->Read($file);
                }
            }

            $attributeValueDataJson = json_encode($attributeValueData);

            return $attributeValueDataJson;
        }
    }

    public function lookupDirectory()
    {
        $path = $this->request->getVar('lookup_dir');

        $fileMan = new SysFileMan($path, true, ['csv', 'xls', 'xlsx', 'phenopacket', 'json']);
        $file_count = count($fileMan->getFiles());
        
        return json_encode($file_count);
    }

    public function importFromDirectory()
    {
        $path = $this->request->getVar('lookup_dir');
        $source_id = $this->request->getVar('source_id');
        $pipeline_id = $this->request->getVar('pipeline_id');
        $user_id = $this->request->getVar('user_id');

        
        $fileMan = new SysFileMan($path, true, ['csv', 'xls', 'xlsx', 'phenopacket', 'json']);
        $unsaved_files = $fileMan->getFiles();
        $files_count = count($unsaved_files);

        $basePath = FCPATH . UPLOAD . UPLOAD_DATA;
        $fileMan = new SysFileMan($basePath);

        foreach ($unsaved_files as $key => $file) {
            if ($fileMan->isValid($file)) {
                $source_path = $source_id . DIRECTORY_SEPARATOR;
                
                if (!$fileMan->Exists($source_id)) {
                    $fileMan->CreateDirectory($source_id);
                }

                if ($fileMan->Save($file, $source_path)) {
                    $file_name = $file->getName();
                    $file_id = $this->uploadModel->createUpload($file_name, $source_id, $user_id, false, false, null, $pipeline_id);
                    unset($unsaved_files[$key]);

                    switch (strtolower($files->getExtension())) {
                        case 'csv':
                        case 'xls':
                        case 'xlsx':
                            $this->phpshellHelperInstance->runAsync(getcwd() . "/index.php Task bulkUploadInsert $file_id 00");
                            break;
                        case 'phenopacket':
                            $this->phpshellHelperInstance->runAsync(getcwd() . "/index.php Task phenoPacketInsertByFileId $file_id " . UPLOADER_DELETE_FILE);
                            break;
                    }
                }
            }
        }

        $unsaved_files_count = count($unsaved_files);

        $result = ["unsaved_count" => $unsaved_files_count,
                   "saved_count" =>  $files_count - $unsaved_files_count];
        
        return json_encode($result);

    }
 }