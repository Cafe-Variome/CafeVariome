<?php namespace App\Controllers;

/**
 * AjaxApi.php 
 * 
 * Created 15/08/2019
 * 
 * @author Mehdi Mehtraizadeh
 * @author Gregory Warren
 * @author Owen Lancaster
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
use App\Models\Source;
use App\Models\Network;
use App\Models\Elastic;
use App\Models\Deliver;
use App\Libraries\CafeVariome\Core\IO\FileSystem\FileMan;
use App\Libraries\CafeVariome\ShellHelper;
use App\Libraries\ElasticSearch;
use Elasticsearch\ClientBuilder;
use App\Libraries\CafeVariome\Core\DataPipeLine\Stream\DataStream;

 class AjaxApi extends Controller{

	protected $db;

    protected $setting;
    
    private $shellHelperInstance;

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

        $this->shellHelperInstance = new ShellHelper();
    }

    function query($network_key = '') {
        $networkInterface = new NetworkInterface();

        $queryString = json_encode($this->request->getVar('jsonAPI'));
        $query_id = $this->getQID();

        $primQueryObj = json_decode($queryString)[1];
        $primQueryObj->meta->components->queryIdentification->queryID = $query_id;
        $primQuery = json_encode($primQueryObj);


        $user_id = $this->request->getVar('user_id');

        $results = [];

        $this->prepare_to_send(json_encode($primQueryObj), $this->setting->settingData['installation_key'], $query_id);


        $cafeVariomeQuery = new \App\Libraries\CafeVariome\Query();
        $loaclResults = $cafeVariomeQuery->search($primQuery, $network_key, $user_id); // Execute locally
        
        //array_push($results, $loaclResults);

        $response = $networkInterface->GetInstallationsByNetworkKey((int)$network_key); // Get other installations within this network
        $installations = [];

        if ($response->status) {
            $installations = $response->data;

            foreach ($installations as $installation) {
                if ($installation->installation_key != $this->setting->settingData['installation_key']) {
                    $secQueryObj = json_decode($queryString)[0];
                    $secQueryObj->meta->components->queryIdentification->queryID = $query_id;
                    $secQuery = json_encode($secQueryObj);
                    
                    $this->prepare_to_send($secQuery, $installation->installation_key, $query_id);
                    // Send the query
                    $queryNetInterface = new QueryNetworkInterface($installation->base_url);
                    $queryResponse = $queryNetInterface->query($secQuery, (int) $network_key, $user_id);
                    //if ($queryResponse->status) {
                        //array_push($results, $queryResponse->data);
                    //}
                }
            }
        }

        return json_encode($results);
    }

    private function prepare_to_send($query,$installation_key,$query_id) {
		
		/// we need to record what the current query we are going to poll
		// then we need to know whether it has been fulfilled or not and whether we need to keep polling.

        $file_name = md5(uniqid(rand(),true));
        $data_path = FCPATH . "upload/query/";
        if (!file_exists($data_path)) {
            mkdir($data_path);
        }
        $data_path = $data_path . $file_name.".json";
        file_put_contents($data_path, json_encode($query));
        $deliverModel = new Deliver();
        $deliverModel->addQueryRecord($query_id,$installation_key,$file_name);


		//Here we loop through sending the actual query to all installations in the network
    }

    private function getQID()
    {
        return md5(uniqid(rand(),true));
    }

    function hpo_query($id = ''){
        if($id) {
            return file_get_contents("https://www240.lamp.le.ac.uk/hpo/query.php?id=" . $id);
        }
        else {
            return file_get_contents("https://www240.lamp.le.ac.uk/hpo/query.php");
        }
    }

    function build_tree() {
        if ($this->request->isAJAX())
        {
            $hpo_json = json_decode(stripslashes($this->request->getVar('hpo_json')), 1);
            // $hpo_json = $_POST['hpo_json'];
            $hpo_json = json_decode(str_replace('"true"', 'true', json_encode($hpo_json)), 1);
            $hpo_json = json_decode(str_replace('"false"', 'false', json_encode($hpo_json)), 1);
    
            error_log(json_encode($hpo_json));
    
            $ancestry = $this->request->getVar('ancestry');
            $hp_term = explode(' ', $this->request->getVar('hp_term'))[0];
    
            error_log($ancestry);
    
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
                                    $dat = file_get_contents("https://www240.lamp.le.ac.uk/hpo/query.php?id=" . $str);
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
        //if ($this->request->isAJAX())
         {
            $networkInterface = new NetworkInterface();
            $response = $networkInterface->GetInstallationsByNetworkKey((int)$network_key);

            $installations = [];

            if ($response->status) {
                $installations = $response->data;
            }

            $postdata = http_build_query(
                array(
                    'network_key' => $network_key,
                    'modification_time' => @filemtime("resources/phenotype_lookup_data/local_" . $network_key . ".json")
                )
            );
    
            $opts = array('http' =>
                array(
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $postdata
                    //'timeout' => 10 //Removed timeout
                )
            );
            $context = stream_context_create($opts);
    
            $data = array();
    
            foreach ($installations as $installation) {
                $url = rtrim($installation->base_url, "/") . "/AjaxApi/get_json_for_phenotype_lookup";
                try{
                    $result = file_get_contents($url, 1, $context);
                }
                catch (\Exception $ex) {
                    error_log($ex->getMessage());
                }
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
                file_put_contents("resources/phenotype_lookup_data/local_" . $network_key . ".json", json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE));
            }
    
            // HPO ancestry
            $postdata = http_build_query(
                ['network_key' => $network_key,
                    'modification_time' => @filemtime("resources/phenotype_lookup_data/" . "local_" . $network_key . "_hpo_ancestry.json")]
            );
    
            $opts = ['http' =>
                [
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $postdata
                    //'timeout' => 1 // Removed timeout 
                ]
            ];
            $context = stream_context_create($opts);
            $hpoDataString = '';
            foreach ($installations as $installation) {
                $url = rtrim($installation->base_url, "/") . "/AjaxApi/get_json_for_hpo_ancestry";
                $hpoDataString = @file_get_contents($url, 1, $context);
                if ($hpoDataString) {
                    file_put_contents("resources/phenotype_lookup_data/" . "local_" . $network_key . "_hpo_ancestry.json", json_encode($hpoDataString)); 
                }
            }
    
            $phen_data = json_decode(file_get_contents("resources/phenotype_lookup_data/" . "local_" . $network_key . ".json"), 1);
            $hpo_data = [];//json_decode(file_get_contents("resources/phenotype_lookup_data/" . "local_" . $network_key . "_hpo_ancestry.json"), 1);
            return json_encode([$phen_data, $hpo_data]);
        }
    }

    function getPhenotypeAttributesHDRSprint(int $network_key) {
        
        $networkModel = new Network();

        $networkInterface = new NetworkInterface();
        $response = $networkInterface->GetInstallationsByNetworkKey((int)$network_key);

        $installations = [];

        if ($response->status) {
            $installations = $response->data;
        }

        $installations_keys = [];
        foreach ($installations as $installation) {
            array_push($installations_keys, $installation-> installation_key);
        }
        $networkModel->removeInstallations($installations_keys, $network_key);
        $networkModel->addInstallations($installations_keys, $network_key);

        $check_sums = $networkModel->getOldChecksums($network_key);

        $dataStream = new DataStream();
        
        foreach ($installations as $installation) {
            $i_url = $installation->base_url;
            $i_key = $installation->installation_key;
            $sum = $check_sums[$i_key];

            $queryNetInterface = new QueryNetworkInterface($installation->base_url);
            $chksumResp = $queryNetInterface->getJSONDataModificationTime((int) $network_key, $sum, false, true);
            $data = [];
            if ($chksumResp->status) {
                $data = $chksumResp->data;
            }

            if ($data->checksum) {
                $networkModel->updateChecksum($data->checksum, $network_key, $i_key);
                $dataStream->regenerateElasticSearchIndex($network_key, $i_key, $data->file);
            }
        }

        $hpo_sums = $networkModel->getOldHPOSums($network_key);
        foreach ($installations as $installation) {
            $i_url = $installation->base_url;
            $i_key = $installation->installation_key;
            $sum = $hpo_sums[$i_key];

            $queryNetInterface = new QueryNetworkInterface($installation->base_url);
            $chksumResp = $queryNetInterface->getJSONDataModificationTime((int) $network_key, $sum, false, false);
            $data = [];
            if ($chksumResp->status) {
                $data = $chksumResp->data;
            }

            if ($data->checksum) {
                $dataStream->pre_hpo_complete($installations, $network_key);
                break;
            }
        }
        $hpo_data = json_decode(file_get_contents("resources/phenotype_lookup_data/" . "local_" . $network_key . "_hpo_ancestry.json"), 1);
        $phen_data = json_decode(file_get_contents("resources/phenotype_lookup_data/" . $network_key . ".json"), 1);
        echo json_encode([$phen_data,$hpo_data]);
    }

    function get_json_for_phenotype_lookup() {

        $modification_time = $this->request->getVar('modification_time');
        $network_key = $this->request->getVar('network_key');

        if (file_exists('resources/phenotype_lookup_data/' . $network_key . ".json")) {
            return (file_get_contents("resources/phenotype_lookup_data/" . $network_key . ".json"));
        } else {
            return false;
        }              
    }

    
    function get_json_for_hpo_ancestry() {
        $modification_time = $this->request->getVar('modification_time');
        $network_key = $this->request->getVar('network_key');

        if (file_exists('resources/phenotype_lookup_data/' . $network_key . "_hpo_ancestry.json")) {
            return (file_get_contents("resources/phenotype_lookup_data/" . $network_key . "_hpo_ancestry.json"));
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
        $source_id = $_POST['source_id'];
        
        // check if it exists
        
        $sourceExists = $this->sourceModel->getSource($source_id);
        if ($sourceExists) {
            // Since it exists get its source id and then check if its locked
            $isLocked = $this->sourceModel->isSourceLocked($source_id);
            if (!$isLocked) {					
                // if its not locked check if we have enough space on the server
                $free = diskfreespace(FCPATH);
                $space_needed = $_POST['size'];		     
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

        $source_id = $_POST['source_id'];
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

        $fileMan = new FileMan($basePath);
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
        $source_id = $_POST['source_id'];
        $user_id = $_POST['user_id'];
        // Get ID for source and lock it so further updates and uploads cannot occur
        // Until update is finished
        $this->sourceModel->toggleSourceLock($source_id);
        $uid = md5(uniqid(rand(),true));
        $this->uploadModel->addUploadJobRecord($source_id,$uid,$user_id);

        // Create thread to begin SQL insert in the background
        $this->shellHelperInstance->runAsync(getcwd() . "/index.php Task phenoPacketInsert ".$source_id);

        // Report to front end that the process has now begun
        echo json_encode("Green");
    }

    
    public function checkUploadJobs() {

        $user_id = $_POST['user_id'];
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
        $source_id = $_POST['source_id'];

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

        $source_id = $_POST['source_id']; 
        $uid = $_POST['uid'];
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

        $uid = $_POST['uid'];

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
        catch (Exception $e) {
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
        $ff = $_FILES;
        $basePath = FCPATH . UPLOAD . UPLOAD_DATA;
        $fileMan = new FileMan($basePath);

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
                
                $file_id = $this->uploadModel->createUpload($file_name, $source_id, $user_id);

                // Begin background insert to MySQL

                $fAction = $this->request->getVar('fAction'); // File Action 
                if ($fAction == "overwrite") {
                    $this->shellHelperInstance->runAsync(getcwd() . "/index.php Task bulkUploadInsert $file_name 1 $source_id");
                }
                elseif ($fAction == "append") {
                    $this->shellHelperInstance->runAsync(getcwd() . "/index.php Task bulkUploadInsert $file_name 00 $source_id");
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


    function delete_file($pFilename) {
        if ( file_exists($pFilename) ) {
            //    Added by muhammad.begawala
            //    '@' will stop displaying "Resource Unavailable" error because of file is open some where.
            //    'unlink($pFilename) !== true' will check if file is deleted successfully.
            //  Throwing exception so that we can handle error easily instead of displaying to users.
            if( @unlink($pFilename) !== true )
                throw new Exception('Could not delete file: ' . $pFilename . ' Please close all applications that are using it.');
        }   
        return true;
    }

    /**
     * Just for HDR Sprint
     * @author Gregory Warren
     * 
     */
    public function searchonindex(int $network_key, $attribute, $val=null) {
        $hosts = array($this->setting->settingData['elastic_url']);
        $this->elasticClient =  ClientBuilder::create()->setHosts($hosts)->build();

        $elasticModel = new Elastic();

        $title = $elasticModel->getTitlePrefix();
        
        $index_name = $title . "_autocomplete_" . $network_key;
        
		$attribute = urldecode($attribute);
    	if ($val) {
    		$paramsnew = ['index' => $index_name, 'size' => 20];
			$paramsnew['body']['query']['bool']['must'][0]['has_parent']['parent_type'] = "att"; 
	    	$paramsnew['body']['query']['bool']['must'][0]['has_parent']['query']['bool']['must'][0]['match']['attribute.raw'] = $attribute;
    	 	$paramsnew['body']['query']['bool']['must'][1]['match']['type_of_doc'] = "overall";
            
            $jp = json_encode($paramsnew);
             $hits = $this->elasticClient->search($paramsnew);
			if (!empty($hits['hits']['hits'])) {
				echo json_encode($hits['hits']['hits'][0]['_source']['value']);
				return;
			}
			$paramsnew = [];
			$paramsnew = ['index' => $index_name];
			$paramsnew['body']['query']['bool']['must'][0]['has_parent']['parent_type'] = "att"; 
	    	$paramsnew['body']['query']['bool']['must'][0]['has_parent']['query']['bool']['must'][0]['match']['attribute.raw'] = $attribute;
	    	$paramsnew['body']['aggs']['attributes']['terms']['field'] = "value";
	    	$paramsnew['body']['aggs']['attributes']['terms']['size'] = "200";
            $jp = json_encode($paramsnew);

            $hits = $this->elasticClient->search($paramsnew);
	    	$resp = [];
	    	foreach ($hits['aggregations']['attributes']['buckets'] as $hit) {
	    		$resp[] = $hit['key'];
	    	}
	    	echo json_encode($resp);
    	}
    	else {
    		$paramsnew = ['index' => $index_name, 'size' => 10];
	    	$paramsnew['body']['query']['match']['attribute']['query'] = $attribute;
	    	$paramsnew['body']['query']['match']['attribute']['operator'] = "and";
	    	$hits = $this->elasticClient->search($paramsnew);
	    	// error_log(print_r($hits,1));
	    	$resp = [];
	    	foreach ($hits['hits']['hits'] as $key => $value) {
	    		$resp[] = $value['_source']['attribute']; 
	    	}
	    	echo json_encode($resp);
    	}
	
	}
 }