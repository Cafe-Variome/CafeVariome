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
    public function json($source) {
        // Check if user is logged in and admin
        //if ($this->ion_auth->in_group("curator")) { 
            // Since this is a shared function for curators and admin check that the curator is a curator for this source
            $user_id = $this->authAdapter->getUserId();
            $source_id = $this->sourceModel->getSourceIDByName($source);

            $can_curate_source = $this->sourceModel->canCurateSource($source_id, $user_id);
            if (!$can_curate_source) {
                //show_error("Sorry, you are not listed as a curator for that particular source.");
            }
        //}
        // data for hidden input for source
        $uidata = new UIData();
        $uidata->title = "Upload JSON (Bulk Import)";
        $uidata->data['source'] = $source;
        // preparing webpage
        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
        $uidata->javascript = [VENDOR.'datatables/datatables/media/js/jquery.dataTables.js',JS. 'bootstrap-notify.js',JS.'cafevariome/vcf.js',JS.'cafevariome/status.js',];

        $data = $this->wrapData($uidata);
        return view('upload/json', $data);
    }

    /**
     * VCF - Render the Stand-Alone view to upload VCF's
     * NOT BEING USED / DEPRECATED
     *
     * @return N/A
     */
    public function vcf($source_id) {

        $uidata = new UIData();
        //if ($this->ion_auth->in_group("curator")) // Since this is a shared function for curators and admin check that the curator is a curator for this source
        $user_id = $this->authAdapter->getUserId();
        $can_curate_source = $this->sourceModel->canCurateSource($source_id, $user_id);
        if (!$can_curate_source) {
                //show_error("Sorry, you are not listed as a curator for that particular source.");
            }
        $uidata->data['source'] = $source_id;
        //$uidata->css = array(CSS.'datatables/datatables/media/css/jquery.dataTables.min.css');

        $uidata->css = ['upload_data.css'];
        $uidata->javascript = [JS.'cafevariome/vcf.js',JS.'cafevariome/status.js'];

        $data = $this->wrapData($uidata);
        return view('upload/vcf', $data);
    }

    /**
     * validateUpload - Ensure the source we are wanting to upload to is an actual source
     * Users can change the parameter on url to what they wish
     * Check if the source is locked by another update/upload operation
     * Perform check that there is enough space on the webserver to upload given file/files
     * Echo result to js front end to determine response to user
     * @param string $_POST['source'] - The source name we will be uploading to and checking against
     * @param int $_POST['size']      - The size in bytes of file/files to be uploaded
     * @return string Green(Success)|Yellow(Not enough space on server)|Red(Source is locked)
        Red(Source doesnt exist)
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

            // if it doesnt conform to expectation
            // TODO: Make it return failure and reflect in JS for this eventuality
            if ($mime != "text/plain" && $file_parts['extension'] == "json" ) {
                error_log("failure");
            }   
            
            if($userFile['userfile'][$i]->move($source_path. "/", $_FILES['userfile']['name'][$i]))
            {     
                // if file upload was successful
                // Update UploadDataStatus table with the new file    
                $this->uploadModel->createUpload($_FILES['userfile']['name'],$source_id);
      
                return true;
            }
            //else
            {
                // if it failed to upload report error
                // TODO: Make it return failure and reflect in JS for this eventuality

            }
        }
        return false;
        
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
    

    

 }