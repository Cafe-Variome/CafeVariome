<?php namespace App\Controllers;


/**
 * Name: Upload.php
 * Created: 31/07/2019
 * 
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 * 
 */

use App\Models\UIData;
use App\Models\Source;
use App\Libraries\CafeVariome\Core\IO\FileSystem\FileMan;
use CodeIgniter\Config\Services;


 class Upload extends CVUI_Controller{

    protected $sourceModel;

    protected $uploadModel;

    /**
	 * Constructor
	 *
	 */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger){
        parent::setProtected(true);
        parent::setIsAdmin(true);
        parent::initController($request, $response, $logger);

        $this->sourceModel = new Source($this->db);
        $this->uploadModel = new \App\Models\Upload($this->db);
    }

    /**
     * Json - render the upload json view
     *
     * @param string $source - The source name we will be uploading to
     * @return N/A
     */
    public function Json($source_id) {
        // Check if user is logged in and admin
        // Since this is a shared function for curators and admin check that the curator is a curator for this source
        $user_id = $this->authAdapter->getUserId();
        //$source_id = $this->sourceModel->getSourceIDByName($source);

        // data for hidden input for source
        $uidata = new UIData();
        $uidata->title = "Upload JSON (Bulk Import)";
        $uidata->data['user_id'] = $user_id;
        $uidata->data['source_id'] = $source_id;
        $uidata->data['source_name'] = $this->sourceModel->getSourceNameByID($source_id);

        // preparing webpage
        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
        $uidata->javascript = [VENDOR.'datatables/datatables/media/js/jquery.dataTables.js',JS. 'bootstrap-notify.js',JS.'cafevariome/vcf.js',JS.'cafevariome/status.js'];

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory . '/Json', $data);
    }

    /**
     * VCF - Render the Stand-Alone view to upload VCF's
     * NOT BEING USED / DEPRECATED
     *
     * @return N/A
     */
    public function VCF($source_id) {

        $uidata = new UIData();
        $uidata->title = "Upload VCF";
        // Since this is a shared function for curators and admin check that the curator is a curator for this source
        $user_id = $this->authAdapter->getUserId();

        $uidata->data['source_name'] = $this->sourceModel->getSourceNameByID($source_id);
        $uidata->data['source_id'] = $source_id;
        
        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css', CSS.'upload_data.css');

        $uidata->javascript = [VENDOR.'datatables/datatables/media/js/jquery.dataTables.js',JS. 'bootstrap-notify.js',JS.'cafevariome/vcf.js'];

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory . '/VCF', $data);
    }

    function Bulk($source_id = null) {
        // Check whether the user is either an admin or a curator that has the required permissions to do this action

        $uidata = new UIData();
        $uidata->title = "Bulk Import";

        // Since this is a shared function for curators and admin check that the curator is a curator for this source
        $user_id = $this->authAdapter->getUserID();

        $uidata->data['source_name'] = $this->sourceModel->getSourceNameByID($source_id);
        $uidata->data['user_id'] = $user_id;
        $uidata->data['source_id'] = $source_id;

        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
        $uidata->javascript = [VENDOR.'datatables/datatables/media/js/jquery.dataTables.js',JS. 'bootstrap-notify.js',JS.'cafevariome/upload_bulk.js',JS.'cafevariome/status.js'];

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory . '/Bulk', $data);
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

        $source_id = filter_var($_POST['source_id'], FILTER_VALIDATE_INT);
        $files = json_decode(htmlEntities($_POST['files'], ENT_QUOTES));
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
        
        $source_id = $_POST['source_id'];
        // Create the source upload directory if it doesnt exist
        $source_path = FCPATH."upload/UploadData/".$source_id."/";

        if (!file_exists($source_path)) {
            error_log("trying to create path");
            mkdir($source_path);
        }
        // Create the json upload directory within the source directory if it doesnt exist
        $source_path = FCPATH."upload/UploadData/".$source_id."/json";	
        if (!file_exists($source_path)) {
            mkdir($source_path);
        }		

        // Check the number of files we are uploading
        $filesCount = count($_FILES['userfile']['name']);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $userFile = $this->request->getFiles();

        for($i = 0; $i < $filesCount; $i++){     

            // Check the mime and extension for the file we are currently uploading

            $mime = finfo_file($finfo, $_FILES['userfile']['tmp_name'][$i]);

            $file_parts = pathinfo($_FILES['userfile']['name'][$i]);

            // if it doesnt conform to expectation
            // TODO: Make it return failure and reflect in JS for this eventuality
            if ($mime != "text/plain" && $file_parts['extension'] == "json" ) {
                error_log("failure");
            }   
            
            if($userFile['userfile'][$i]->move($source_path. "/", $_FILES['userfile']['name'][$i]))
            {     
                // if file upload was successful
                // Update UploadDataStatus table with the new file    
                $this->uploadModel->createUpload($_FILES['userfile']['name'][$i],$source_id);
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
        // Get ID for source and lock it so further updates and uploads cannot occur
        // Until update is finished
        $this->sourceModel->toggleSourceLock($source_id);
        $uid = md5(uniqid(rand(),true));
        $this->uploadModel->addUploadJobRecord($source_id,$uid,$_SESSION['user_id']);
        // Create thread to begin SQL insert in the background

        shell_exec("php " . getcwd() . "/index.php Task phenoPacketInsert ".$source_id);

        // Report to front end that the process has now begun
        echo json_encode("Green");
    }

    
    public function checkUploadJobs() {

        $user_id = $_SESSION['user_id'];
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
                $this->uploadModel->createUpload($_FILES['userfile']['name'][$i], $source_id,$pairings[$_FILES['userfile']['name'][$i]][0],$pairings[$_FILES['userfile']['name'][$i]][1]);
            }
            else {
                // if it failed to upload report error
                // TODO: Make it return failure and reflect in JS for this eventuality
                error_log($mime);
                //error_log($this->upload->display_errors());
            }
        }
        echo json_encode("Green");
    }

    public function vcfStart() {
        $source_id = $_POST['source_id'];
        $uid = $_POST['uid'];

        // Get ID for source and lock it so further updates and uploads cannot occur
        // Until update is finished

        $this->sourceModel->toggleSourceLock($source_id);
        $uid = md5(uniqid(rand(),true));
        $this->uploadModel->addUploadJobRecord($source_id,$uid,$this->session->get('user_id'));
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
        error_log(print_r(htmlEntities($_POST, ENT_QUOTES),1));
        $tmp = $_FILES["userfile"]["tmp_name"];
        $file_name = $_FILES["userfile"]["name"];
        $source_id = $_POST['source_id'];
        if (!$force) {
            if ($this->uploadModel->isDuplicatePhysicalFile($source_id,$file_name, $tmp)) {
                $response_array = array('status' => "Duplicate");
                echo json_encode($response_array);
                return;
            }
        }
        
        $source_path = FCPATH."upload/UploadData/".$source_id;

        
        $userFile = $this->request->getFiles();

        if ($userFile['userfile']->move($source_path. "/", $_FILES['userfile']['name'])){	
            // File was uploaded successfully	
            // Populate status table with initial details on uploaded file
            $file_id = $this->uploadModel->createUpload($file_name, $source_id);
            // Begin background insert to MySQL
            if ($_POST['fAction'][0] == "overwrite") {
                error_log("overwriting");
                shell_exec("php " . getcwd() . "/index.php Task bulkUploadInsert ".$file_name." 1 ".$source_id);
            }
            elseif ($_POST['fAction'][0] == "append") {
                error_log("appending");
                shell_exec("php " . getcwd() . "/index.php Task bulkUploadInsert ".$file_name." 00 ".$source_id);
            }
            else {
                error_log("entered else");
                return;
            }	
            $uid = md5(uniqid(rand(),true));
            $this->uploadModel->addUploadJobRecord($source_id,$uid,$this->session->get('user_id'));
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
 }