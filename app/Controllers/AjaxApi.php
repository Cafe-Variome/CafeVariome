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

        $hpoNetworkInterface = new HPONetworkInterface('https://www240.lamp.le.ac.uk/');
        $results = $hpoNetworkInterface->getHPO($hpo_term);
        return json_encode($results);
    }

    function build_tree() {
        if ($this->request->isAJAX())
        {
            $hpoNetworkInterface = new HPONetworkInterface('https://www240.lamp.le.ac.uk/');

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
    
            $phen_data = json_decode($fileMan->Read("local_" . $network_key . ".json"), 1);
            $hpo_data = json_decode($fileMan->Read("local_" . $network_key . "_hpo_ancestry.json"), 1);
            return json_encode([$phen_data, $hpo_data]);
        }
    }

    /**
     * @deprecated */
    private function get_json_for_phenotype_lookup() {
        $basePath = FCPATH . JSON_DATA_DIR;

        $fileMan = new SysFileMan($basePath);
        $modification_time = $this->request->getVar('modification_time');
        $network_key = $this->request->getVar('network_key');

        if ($fileMan->Exists($network_key . ".json")) {
            return $fileMan->Read($network_key . ".json");
        } else {
            return false;
        }              
    }

    
    /**
     * @deprecated */
    private function get_json_for_hpo_ancestry() {
        $basePath = FCPATH . JSON_DATA_DIR;

        $fileMan = new SysFileMan($basePath);
        $modification_time = $this->request->getVar('modification_time');
        $network_key = $this->request->getVar('network_key');

        if ($fileMan->Exists($network_key . "_hpo_ancestry.json")) {
            return $fileMan->Read($network_key . "_hpo_ancestry.json");
		}
		else {
            return false;
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
     * @param arrray $_POST['files']  - The list of files we must check presence for
     * @param string $_POST['source'] - The source we must check presense for inside
     * @return string Green | json_encoded array with list of files
     */
    public function checkJsonPresence() {

        $source_id = $this->request->getVar('source_id');
        $files = json_decode($_POST['files']);
        // Check if there are duplicates
        $duplicates = $this->uploadModel->checkJsonFiles($files,$source_id);
        if ($duplicates) {
            echo json_encode($duplicates);
        }
        else {
            echo json_encode("Green");
        }
    }

    /**
     * Json Batch - At this point all checks have been performed. Upload the json files in
     * Batches of 20 (as limited by php.ini)
     *
     * @param arrray $_FILES          - The list of files we must upload
     * @param string $_POST['source'] - The source we must upload into
     * @return N/A
     */
    public function jsonBatch() {
        
        $source_id = $this->request->getVar('source_id');
        $user_id = $this->request->getVar('user_id');

        $basePath = FCPATH . UPLOAD . UPLOAD_DATA;
        // Create the source upload directory if it doesnt exist
        $source_path =  $source_id;

        $fileMan = new UploadFileMan($basePath);
        if (!$fileMan->Exists($source_path)) {
            $fileMan->CreateDirectory($source_path);
        }

        $source_path =  $source_id . DIRECTORY_SEPARATOR . UPLOAD_JSON;

        if (!$fileMan->Exists($source_path)) {
            $fileMan->CreateDirectory($source_path);
        }

        // Check the number of files we are uploading
        $filesCount = $fileMan->countFiles(); //count($_FILES['userfile']['name']);

        $files = $fileMan->getFiles();

        foreach ($files as $file) {
           
            // Check the mime and extension for the file we are currently uploading
            $mime = $file->getType();

            // if it doesnt conform to expectation
            // TODO: Make it return failure and reflect in JS for this eventuality
            if ($mime != "text/plain" && $file->getExtension() == "json" ) {
                error_log("failure");
            }   
            
            if($fileMan->Save($file, $source_path))
            {     
                // if file upload was successful
                // Update UploadDataStatus table with the new file    
                $this->uploadModel->createUpload($file->getName(),$source_id, $user_id);
            }
            else
            {
                // if it failed to upload report error
                // TODO: Make it return failure and reflect in JS for this eventuality
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
        $this->sourceModel->toggleSourceLock($source_id);
        $uid = md5(uniqid(rand(),true));
        $this->uploadModel->addUploadJobRecord($source_id,$uid,$user_id);

        // Create thread to begin SQL insert in the background
        $this->phpshellHelperInstance->runAsync(getcwd() . "/index.php Task phenoPacketInsert ".$source_id);

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

        $spreadsheet =  \PhpOffice\PhpSpreadsheet\IOFactory::load($_FILES['config']['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow(); 
        $highestColumn = $worksheet->getHighestColumn(); 
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        $_POST['files'] = explode(",", $_POST['files']);

        $headers = [];
        $dup_files = [];
        $dup_elastic = [];
        $pairings = [];
        $types = [];
        $source_id = $this->request->getVar('source_id');

        array_push($headers, "");
        $response_array = array('status' => "",
                                        'message' => []);

        $file_parts = pathinfo($_FILES['config']['name']);

        $count = count($_POST['files']);
        if ($count > 200) {
            error_log("overload");
            $response_array['status'] = "Overload";
            $response_array['message'] = "You are trying to upload more than 200 files. Please limit your upload to 200 files or less.";
            echo json_encode($response_array);
            return;
        }
        if ($file_parts['extension'] == "csv" || $file_parts['extension'] == "xls") {
            for ($row = 1; $row <= $highestRow; ++$row) {
                for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                    if ($row == 1) {
                        $value = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                        if (!preg_match("/filename|patient|tissue/", strtolower($value))) {
                            error_log("failure on ".$value);
                            $message = "Headers in ".$_FILES['config']['name']." not in FileName,Patient,Tissue format.";
                            array_push($response_array['message'], $message);
                            echo json_encode($response_array);
                            return;
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
                                if (is_array($_POST['files'])) {
                                    if (in_array($value, $_POST['files'])) $flag = 1;
                                }
                                else {
                                    if ($value == $_POST['files']) $flag = 1;
                                }	
                                if (!$flag) {
                                    $message = "File: ".$value." not found in list of Uploaded Files from config file: ". $_FILES['config']['name'];
                                    array_push($response_array['message'], $message);			
                                }
                                if (!preg_match("/\.vcf$|\.vcf\.gz$/", $value)) {
                                    $message = "File: ".$value." is not a vcf file.";
                                    array_push($response_array['message'], $message);
                                }
                                $file_path = FCPATH."upload/UploadData/".$source_id."/".$value;
                                if (file_exists($file_path)) {
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
            echo json_encode($response_array);
            return;		
        }
        $source_path = FCPATH."upload/UploadData/$source_id";
        if (!file_exists($source_path)) mkdir($source_path);
        $source_path = FCPATH."upload/pairings/";
        if (!file_exists($source_path)) mkdir($source_path); 
        $uid = md5(uniqid(rand(),true));  
        file_put_contents($source_path.$uid.".json", json_encode($pairings));
        $response_array['uid'] = $uid;
        $response_array['types'] = $types;
        echo json_encode($response_array);
        return;
    }

    public function vcfBatch() {

        $source_id = $this->request->getVar('source_id'); 
        $uid = $this->request->getVar('uid');

        $pairings = json_decode(file_get_contents(FCPATH."upload/pairings/$uid.json"), true);
        $source_path = FCPATH."upload/UploadData/".$source_id."/";	

        // Params for the upload type		
        $config = array(
            'upload_path' => $source_path,
            'allowed_types' => "*",
            'overwrite' => TRUE,
            'max_size' => "2048000" // Can be set to particular file size , here it is 2 MB(2048 Kb)
        );

        // Check the number of files we are uploading
        $filesCount = count($_FILES['userfile']['name']);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $userFile = $this->request->getFiles();

        for($i = 0; $i < $filesCount; $i++){     
            // Check the mime and extension for the file we are currently uploading
            $mime = finfo_file($finfo, $_FILES['userfile']['tmp_name'][$i]);
            $file_parts = pathinfo($_FILES['userfile']['name'][$i]);
            if ($mime != "text/plain" && $file_parts['extension'] == "json") {
                error_log("failure");
            }   

            if($userFile['userfile'][$i]->move($source_path. "/", $_FILES['userfile']['name'][$i]))
            {   
                // 13/08/2019 POTENTIAL BUG 
                // The value for patient must be specified as it is always set to 0 (false)
                $this->uploadModel->createUpload($_FILES['userfile']['name'][$i], $source_id, $user_id, $pairings[$_FILES['userfile']['name'][$i]][0],$pairings[$_FILES['userfile']['name'][$i]][1]);
            }
            else {
                // if it failed to upload report error
                // TODO: Make it return failure and reflect in JS for this eventuality
                error_log($mime);
            }
        }
        echo json_encode("Green");
    }

    public function vcfStart() {
        $source_id = $this->request->getVar('source_id');
        $user_id = $this->request->getVar('user_id');
        $uid = $this->request->getVar('uid');

        // Get ID for source and lock it so further updates and uploads cannot occur
        // Until update is finished

        $this->sourceModel->toggleSourceLock($source_id);
        $uid = md5(uniqid(rand(),true));
        $this->uploadModel->addUploadJobRecord($source_id,$uid,$user_id);
        $path = FCPATH."upload/pairings/".$uid.".json";
        try {
            if( $this->delete_file($path) === true ) {
                error_log(" delete success");	    	
            }
        }
        catch (\Exception $e) {
            error_log($e->getMessage()); // will print Exception message defined above.
        }
        // Create thread to begin SQL insert in the background
        error_log("calling vcfElastic");
        echo json_encode("Green");
        shell_exec("php " . getcwd() . "/index.php Task vcfElastic ". $source_id);
        
        // Report to front end that the process has now begun
        
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

        $basePath = FCPATH . UPLOAD . UPLOAD_DATA;
        $fileMan = new UploadFileMan($basePath);

        if ($fileMan->countFiles() == 1){ // Only 1 file is allowed to go through this uploader
            $file = $fileMan->getFiles()[0];
            $tmp = $file->getTempPath();
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
                
                $file_id = $this->uploadModel->createUpload($file_name, $source_id, $user_id);

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
        $uploadModel->resetFileStatus($fileId);

        if ($overwrite) {
            $task = new Task();
            $task->bulkUploadInsert($fileId, 1);
            $exec = $this->phpshellHelperInstance->run(getcwd() . "/index.php Task bulkUploadInsert $fileId 1");
        }
        else{
            $exec = $this->phpshellHelperInstance->run(getcwd() . "/index.php Task bulkUploadInsert $fileId 00"); //Don't change 00 to 0 as it won't be detected on Windows machines.
        }

        return json_encode(0);
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
 }