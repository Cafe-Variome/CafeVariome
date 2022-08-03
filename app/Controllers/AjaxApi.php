<?php namespace App\Controllers;

/**
 * AjaxApi.php
 *
 * Created 15/08/2019
 *
 * @author Mehdi Mehtraizadeh
 * @author Gregory Warren
 *
 * This controller contains listener methods for client-side ajax requests.
 * Methods in this controller were formerly in other controllers.
 * Code must be more secure. Some of the methods here must be moved to back-end layers for security reasons.
 */

use App\Libraries\CafeVariome\Auth\LocalAuthenticator;
use App\Libraries\CafeVariome\CafeVariome;
use App\Libraries\CafeVariome\Core\DataPipeLine\Index\UserInterfaceNetworkIndex;
use App\Libraries\CafeVariome\Core\IO\FileSystem\File;
use App\Libraries\CafeVariome\Factory\AuthenticatorFactory;
use App\Libraries\CafeVariome\Factory\DataFileAdapterFactory;
use App\Libraries\CafeVariome\Factory\DataFileFactory;
use App\Libraries\CafeVariome\Factory\PipelineAdapterFactory;
use App\Libraries\CafeVariome\Factory\SingleSignOnProviderAdapterFactory;
use App\Libraries\CafeVariome\Factory\SourceAdapterFactory;
use App\Libraries\CafeVariome\Factory\TaskAdapterFactory;
use App\Libraries\CafeVariome\Factory\TaskFactory;
use CodeIgniter\Controller;
use Config\Database;
use App\Libraries\CafeVariome\Net\NetworkInterface;
use App\Libraries\CafeVariome\Net\QueryNetworkInterface;
use App\Models\Upload;
use App\Libraries\CafeVariome\Core\IO\FileSystem\UploadFileMan;
use App\Libraries\CafeVariome\Core\IO\FileSystem\SysFileMan;
use App\Libraries\CafeVariome\Helpers\Shell\PHPShellHelper;

class AjaxApi extends Controller
{
	protected $db;

    protected $setting;

	protected $authenticator;

	protected const LOCAL_AUTHENTICATION = ALLOW_LOCAL_AUTHENTICATION;
	protected const AUTHENTICATOR_SESSION = AUTHENTICATOR_SESSION_NAME;
	protected const SSO_RANDOM_STATE_SESSION = SSO_RANDOM_STATE_SESSION_NAME;
	protected const SSO_TOKEN_SESSION = SSO_TOKEN_SESSION_NAME;
	protected const SSO_REFRESH_TOKEN_SESSION = SSO_REFRESH_TOKEN_SESSION_NAME;
	protected const POST_AUTHENTICATION_REDIRECT_URL_SESSION = POST_AUTHENTICATION_REDIRECT_URL_SESSION_NAME;

    /**
	 * Constructor
	 *
	 */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		parent::initController($request, $response, $logger);

		$this->db = Database::connect();
        $this->setting =  CafeVariome::Settings();

		$this->session = \Config\Services::session();

		if ($this->session->has(self::AUTHENTICATOR_SESSION))
		{
			if (intval($this->session->get(self::AUTHENTICATOR_SESSION)) > 0)
			{
				$authenticatorFactory = new AuthenticatorFactory();
				$provider = (new SingleSignOnProviderAdapterFactory())->GetInstance()->Read($this->session->get(self::AUTHENTICATOR_SESSION));
				if (!$provider->isNull())
				{
					$this->authenticator = $authenticatorFactory->GetInstance($provider);
				}
				else
				{
					$this->session->destroy();
				}
			}
			else
			{
				if (self::LOCAL_AUTHENTICATION)
				{
					// Local authenticator being used
					$this->authenticator = new LocalAuthenticator();
				}
			}

		}
    }

    public function query()
	{
        $networkInterface = new NetworkInterface();

		$session = \Config\Services::session();

        //Check to see if user is logged in
        if (!$session->has(AUTHENTICATOR_SESSION_NAME))
		{
			return json_encode(['timeout' => 'Your session has timed out. You need to login again.']);
		}
		$authenticatorFactory = new AuthenticatorFactory();
		$authenticator = $authenticatorFactory->GetInstance(
			(new SingleSignOnProviderAdapterFactory())->GetInstance()->Read(
				$session->get(AUTHENTICATOR_SESSION_NAME)
			));

		if (!$authenticator->LoggedIn())
		{
			return json_encode(['timeout' => 'Your session has timed out. You need to login again.']);
		}

        $network_key = $this->request->getVar('network_key');
        $queryString = json_encode($this->request->getVar('jsonAPI'));
        $token = $authenticator->GetRefreshToken(['refresh_token' => $session->get(SSO_REFRESH_TOKEN_SESSION_NAME)]);

        $user_id = $authenticator->GetUserIdByToken($token);

        try {
            $results = [];
            $cafeVariomeQuery = new \App\Libraries\CafeVariome\Query\Compiler();
            $loaclResults = $cafeVariomeQuery->CompileAndRunQuery($queryString, $network_key, $user_id); // Execute locally
            array_push($results, $loaclResults);

            $response = $networkInterface->GetInstallationsByNetworkKey((int)$network_key); // Get other installations within this network
            $installations = [];

            if ($response->status) {
                $installations = $response->data;

                foreach ($installations as $installation) {
                    if ($installation->installation_key != $this->setting->getInstallationKey()) {
                        // Send the query
                        $queryNetInterface = new QueryNetworkInterface($installation->base_url);
                        $queryResponse = $queryNetInterface->query($queryString, (int) $network_key, $authenticator->GetBaseURL(), $token);
                        if ($queryResponse->status) {
                            array_push($results, json_encode($queryResponse->data));
                        }
                    }
                }
            }

            return json_encode($results);
        } catch (\Exception $ex) {
            return json_encode(['error' => 'There was a problem executing the query. Please try again with a different query.'.$ex->getMessage()]);
        }
    }

	/**
     * getPhenotypeAttributes
     * @param string network_key
     * @return string in json format, phenotype and hpo data
     *
     */
    public function getPhenotypeAttributes(int $network_key) {
        if ($this->request->getMethod() == 'post')
        {
			$userInterfaceNetworkIndex = new UserInterfaceNetworkIndex($network_key);
			$userInterfaceNetworkIndex->IndexNetworkInstallations();

			$basePath = FCPATH . USER_INTERFACE_INDEX_DIR;
			$fileMan = new SysFileMan($basePath);

			$localData = json_decode($fileMan->Read($network_key . '_local.json'), true);

            return json_encode($localData);
        }
    }

	 /**
	  * elasticStart - Begin ElasticSearch regeneration
	  *
	  * @param int $source_id        - The source id for the elasticsearch index
	  * @param int $append       - 1 if we are adding to index instead of fully regenerating
	  * @return void
	  */
	 public function elasticStart()
	 {
		 if ($this->request->getMethod() == 'post'){
			 $source_id = $this->request->getVar('source_id');
			 $append = $this->request->getVar('append') === 'true' ? 1 : 0;
			 PHPShellHelper::runAsync(getcwd() . "/index.php Task IndexDataToElasticsearch $source_id $append");
	 	}
	 }

	/**
	 * neo4jStart - Begin Neo4J regeneration
	 *
	 * @param int $source_id        - The source id for the neo4j index
	 * @param int $append       - 1 if we are adding to index instead of fully regenerating
	 * @return void
	 */
	public function neo4jStart()
	{
		if ($this->request->getMethod() == 'post'){
			$source_id = $this->request->getVar('source_id');
			$append = $this->request->getVar('append') === 'true' ? 1 : 0;
			PHPShellHelper::runAsync(getcwd() . "/index.php Task IndexDataToNeo4J $source_id $append");
		}
	}

	public function userInterfaceStart()
	{
		if ($this->request->getMethod() == 'post') {
			$source_id = $this->request->getVar('source_id');
			PHPShellHelper::runAsync(getcwd() . "/index.php Task CreateUserInterfaceIndex $source_id");
		}
	}

    /**
	 * @deprecated
     * validateUpload - Ensure the source we are wanting to upload to is an actual source
     * Users can change the parameter on url to what they wish
     * Check if the source is locked by another update/upload operation
     * Perform check that there is enough space on the webserver to upload given file/files
     * Echo result to js front end to determine response to user
     * @param string $source - The source name we will be uploading to and checking against
     * @param int $size      - The size in bytes of file/files to be uploaded
     * @return string Green(Success)|Yellow(Not enough space on server)|Red(Source is locked) Red(Source doesnt exist)
    */

    public function validateUpload() {

		if ($this->request->getMethod() == 'post') {
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
					} else {
						// All checks passed
						return json_encode("Green");
					}
				} else {
					// The source is locked
					return json_encode("Locked");
				}
			} else {
				// The source target doesnt exist
				return json_encode("Red");
			}
		}
    }

    /**
	 * @deprecated
     * Check Json Presence - Check if the server has any of the targeted json files
     * Already present for this source
     *
     * @param array $_POST['files']  - The list of files we must check presence for
     * @param string $_POST['source'] - The source we must check presense for inside
     * @return string Green | json_encoded array with list of files
     */
    public function checkJsonPresence() {
		if ($this->request->getMethod() == 'post') {
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
			} else {
				return json_encode("Green");
			}
		}
    }

    /**
	 * @deprecated
     * Json Batch - At this point all checks have been performed. Upload the json files in
     * Batches of 20 (as limited by php.ini)
     *
     * @param array $_FILES          - The list of files we must upload
     * @param string $_POST['source'] - The source we must upload into
     * @return N/A
     */
    public function jsonBatch() {
		if ($this->request->getMethod() == 'post') {
			$source_id = $this->request->getVar('source_id');
			$user_id = $this->request->getVar('user_id');
			$pipeline_id = $this->request->getVar('pipeline_id');

			$basePath = FCPATH . UPLOAD . UPLOAD_DATA;
			// Create the source upload directory if it doesnt exist
			$source_path = $source_id;

			$fileMan = new UploadFileMan($basePath);
			if (!$fileMan->Exists($source_path)) {
				$fileMan->CreateDirectory($source_path);
			}

			$source_path = $source_id . DIRECTORY_SEPARATOR;

			if (!$fileMan->Exists($source_path)) {
				$fileMan->CreateDirectory($source_path);
			}

			$files = $fileMan->getFiles();

			foreach ($files as $file)
			{
				$error = '';
				if (!$fileMan->isValid($file, $error)) {
					return false;
				}

				if ($fileMan->Save($file, $source_path)) {
					$this->uploadModel->createUpload($file->getName(), $source_id, $user_id, false, false, null, $pipeline_id);
				} else {
					return false;
				}
			}

			return true;
		}
    }

    /**
	 * @deprecated
     * Json Start - At this point all files have been uploaded. Lock the source and begin
     * Insert into MySQL
     *
     * @param string $_POST['source'] - The source we must upload into
     * @return string Green for success
     */
    public function jsonStart() {
		if ($this->request->getMethod() == 'post') {
			// Assign posted source to easier variable
			$source_id = $this->request->getVar('source_id');
			$user_id = $this->request->getVar('user_id');
			// Get ID for source and lock it so further updates and uploads cannot occur
			// Until update is finished
			$this->sourceModel->lockSource($source_id);
			$uid = md5(uniqid(rand(), true));
			$this->uploadModel->addUploadJobRecord($source_id, $uid, $user_id);

			// Create thread to begin SQL insert in the background
			PHPShellHelper::runAsync(getcwd() . "/index.php Task phenoPacketInsertBySourceId " . $source_id . " 00");

			// Report to front end that the process has now begun
			echo json_encode("Green");
		}
    }

    public function vcfUpload() {
		if ($this->request->getMethod() == 'post') {
			$basePath = FCPATH . UPLOAD . UPLOAD_DATA;
			$pairingsPath = FCPATH . UPLOAD . UPLOAD_PAIRINGS;

			$fileMan = new UploadFileMan($basePath);

			$response_array = array('status' => "", 'message' => []);

			if ($fileMan->countFiles() == 1) {
				// Only one config file is allowed to be uploaded at the moment.
				$configFile = $fileMan->getFiles()[0];
				$configFileName = $configFile->getName();
				$configFileExtension = $configFile->getExtension();
				$configFileTempPath = $configFile->getTempPath();
			} else {
				$response_array['status'] = "Cancel";
				$response_array['message'] = "Config file is either not uploaded or is missing.";

				return json_encode($response_array);
			}

			$source_id = $this->request->getVar('source_id');
			$fileNames = $this->request->getVar('files'); // Name of VCF files that will be uploaded pending they meet conditions.
			$fileNamesArray = explode(",", $fileNames); // Array of the file names above

			$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($configFileTempPath);
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
							} else {
								array_push($headers, strtolower($value));
							}
						} else {
							$value = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
							$key = $headers[$col];
							switch ($key) {
								case 'filename' :
									$flag = 0;
									if (is_array($fileNamesArray)) {
										if (in_array($value, $fileNamesArray)) $flag = 1;
									} else {
										if ($value == $fileNamesArray) $flag = 1;
									}
									if (!$flag) {
										$message = "File: " . $value . " not found in list of Uploaded Files from config file: " . $configFileName;
										array_push($response_array['message'], $message);
									}
									if (!preg_match("/\.vcf$|\.vcf\.gz$/", $value)) {
										$message = "File: " . $value . " is not a vcf file.";
										array_push($response_array['message'], $message);
									}

									$file_path = $source_id . DIRECTORY_SEPARATOR . $value;
									if ($fileMan->Exists($file_path)) {
										array_push($dup_files, $value);
									}
									$file = $value;
									break;
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
					if ($this->uploadModel->patientSubjectSourceCombo($source_id, $patient, $tissue)) {
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
				} else if (empty($dup_files) && empty($dup_elastic)) {
					$response_array['status'] = "Green";
					$response_array['message'] = "no errors";
				} else if (!empty($dup_files) && !empty($dup_elastic)) {
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
					} else {
						$response_array['elastic'] = $dup_elastic;
						$response_array['files'] = $dup_files;
						array_push($types, "elastic");
						array_push($types, "files");
					}
				} else {
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
			} else {
				$response_array['status'] = "Cancel";
				array_push($response_array['message'], "Config file is not in correct format. Cannot be read.");

				return json_encode($response_array);
			}

			if (!$fileMan->Exists($source_id)) {
				$fileMan->CreateDirectory($source_id);
			}

			$fileMan = new UploadFileMan($pairingsPath);

			$uid = md5(uniqid(rand(), true));

			$fileMan->Write($uid . ".json", json_encode($pairings));

			$response_array['uid'] = $uid;
			$response_array['types'] = $types;

			return json_encode($response_array);
		}
    }

    public function vcfBatch() {
		if ($this->request->getMethod() == 'post') {
			$basePath = FCPATH . UPLOAD;
			$fileMan = new UploadFileMan($basePath);

			$source_id = $this->request->getVar('source_id');
			$uid = $this->request->getVar('uid');
			$user_id = $this->request->getVar('user_id');
			$pipeline_id = $this->request->getVar('pipeline_id');

			if ($fileMan->Exists(UPLOAD_PAIRINGS . $uid . ".json")) {
				$pairings = json_decode($fileMan->Read(UPLOAD_PAIRINGS . $uid . ".json"), true);
				$source_path = UPLOAD_DATA . $source_id . DIRECTORY_SEPARATOR;

				// Check the number of files we are uploading
				$filesCount = $fileMan->countFiles();
				$userFiles = $fileMan->getFiles();

				for ($i = 0; $i < $filesCount; $i++) {
					// Check the mime and extension for the file we are currently uploading
					$fileName = $userFiles[$i]->getName();
					$mime = $userFiles[$i]->getType();
					$extension = $userFiles[$i]->getExtension();

					if ($mime != "text/vcard" && $extension == "json") {
						error_log("failure");
					}

					if ($fileMan->Save($userFiles[$i], $source_path)) {
						// 13/08/2019 POTENTIAL BUG
						// The value for patient must be specified as it is always set to 0 (false)
						$this->uploadModel->createUpload($fileName, $source_id, $user_id, $pairings[$fileName][0], $pairings[$fileName][1], null, $pipeline_id);
					} else {
						// if it failed to upload report error
						// TODO: Make it return failure and reflect in JS for this eventuality
					}
				}

				return json_encode("Green");
			}
		}
    }

    public function vcfStart() {
		if ($this->request->getMethod() == 'post') {
			$pairingsPath = FCPATH . UPLOAD . UPLOAD_PAIRINGS;
			$fileMan = new UploadFileMan($pairingsPath);

			$source_id = $this->request->getVar('source_id');
			$user_id = $this->request->getVar('user_id');
			$uid = $this->request->getVar('uid');
			$overwrite = $this->request->getVar('fAction');

			// Get ID for source and lock it so further updates and uploads cannot occur until update is finished
			$this->sourceModel->lockSource($source_id);
			$this->uploadModel->addUploadJobRecord($source_id, $uid, $user_id);

			$path = $uid . ".json";

			if ($fileMan->Exists($path)) {
				$fileMan->Delete($path);
			}

			if ($overwrite == "overwrite") {
				PHPShellHelper::runAsync(getcwd() . "/index.php Task vcfInsertBySourceId " . $source_id . " " . UPLOADER_DELETE_ALL);
			} elseif ($overwrite == "append") {
				PHPShellHelper::runAsync(getcwd() . "/index.php Task vcfInsertBySourceId " . $source_id . " " . UPLOADER_DELETE_NONE);
			}

			return json_encode("Green");
		}
    }

    /**
	 * deprecated
     * spreadsheetUpload - Perform Upload for CSV/XLS/XLSX files
     *
     * @param string $source_id - The source name we will be uploading to
     * @param int $user_id
	 * @param int $pipeline_id
	 * @param bool $force
     * @return json_encoded array Success|Headers are not as expected|File is Duplicated
     */
    public function spreadsheetUpload()
	{
		if ($this->request->getMethod() == 'post'){
			$source_id = $this->request->getVar('source_id');
			$user_id = $this->request->getVar('user_id');
			$pipeline_id = $this->request->getVar('pipeline_id');
			$force = $this->request->getVar('force');

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

				$error = '';
				if (!$fileMan->isValid($file, $error))
				{
					$response_array = array(
						'status' => "InvalidFile",
						'error' => $error
					);
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
						PHPShellHelper::runAsync(getcwd() . "/index.php Task spreadsheetInsert $file_id 1 $source_id");
					}
					elseif ($fAction == "append") {
						PHPShellHelper::runAsync(getcwd() . "/index.php Task spreadsheetInsert $file_id 00 $source_id");
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
    }


    public function LookupDirectory()
	{
		if ($this->request->getMethod() == 'post')
		{
			$allowed_formats = ['csv', 'xls', 'xlsx', 'phenopacket'];
			$file_count = 0;
			$path = $this->request->getVar('lookup_dir');

			if (SysFileMan::IsFile($path))
			{
				if(in_array(strtolower(SysFileMan::GetFileExtension($path)), $allowed_formats))
				{
					$file_count = 1;
				}
			}
			else
			{
				$fileMan = new SysFileMan($path, true, $allowed_formats);
				$file_count = count($fileMan->getFiles());
			}

			return json_encode($file_count);
		}
	}

	public function ImportFromDirectory()
	{
		 if ($this->request->getMethod() == 'post')
		 {
			 $allowed_formats = ['csv', 'xls', 'xlsx', 'phenopacket'];

			 $path = $this->request->getVar('lookup_dir');
			 $source_id = $this->request->getVar('source_id');
			 $user_id = $this->request->getVar('user_id');

			 if (SysFileMan::IsFile($path))
			 {
				 if (in_array(strtolower(SysFileMan::GetFileExtension($path)), $allowed_formats))
				 {
					 $files_count = 1;
					 $unsaved_files = [
						 new File(
							 SysFileMan::GetFileName($path),
							 SysFileMan::GetFileSize($path),
							 $path, SysFileMan::GetFileMimeType($path) ,0,
							 true,
							 27
						 )
					 ];
				 }
			 }
			 else
			 {
				 $fileMan = new SysFileMan($path, true, $allowed_formats, true, 27);
				 $unsaved_files = $fileMan->getFiles();
				 $files_count = count($unsaved_files);
			 }

			 $basePath = FCPATH . UPLOAD . UPLOAD_DATA;
			 $fileMan = new SysFileMan($basePath, false, $allowed_formats, true, 27);

			 $dataFileFactory = new DataFileFactory();
			 $dataFileAdapter = (new DataFileAdapterFactory())->GetInstance();

			 foreach ($unsaved_files as $key => $file)
			 {
				 $error = '';
				 if ($fileMan->isValid($file, $error))
				 {
					 $source_path = $source_id . DIRECTORY_SEPARATOR;

					 if (!$fileMan->Exists($source_id))
					 {
						 $fileMan->CreateDirectory($source_id);
					 }

					 if ($fileMan->Save($file, $source_path))
					 {
						 $dataFileAdapter->Create($dataFileFactory->GetInstanceFromParameters(
							 $file->getName(),
							 $file->getDiskName(),
							 $file->getSize(),
							 time(),
							 0,
							 $this->authenticator->GetUserId(),
							 $source_id,
							 DATA_FILE_STATUS_IMPORTED
						 ));

						 unset($unsaved_files[$key]);
					 }
				 }
			 }

			 $unsaved_files_count = count($unsaved_files);

			 $result = [
				 "unsaved_count" => $unsaved_files_count,
				 "saved_count" => $files_count - $unsaved_files_count
			 ];

			 return json_encode($result);
		 }
	}

	/**
	 * @return false|string|void
	 * @throws \Exception
	 */
    public function ProcessFile()
    {
		if ($this->request->getMethod() == 'post')
		{
			$fileId = $this->request->getVar('fileId');
			$pipelineId = $this->request->getVar('pipelineId');

			if(is_null($fileId))
			{
				return json_encode([
					'status' => 1,
					'message' => 'File Id is null.'
				]);
			}

			if(is_null($pipelineId))
			{
				return json_encode([
					'status' => 1,
					'message' => 'Pipeline Id is null.'
				]);
			}

			$dataFileAdapter = (new DataFileAdapterFactory())->GetInstance();
			$pipelineAdapter = (new PipelineAdapterFactory())->GetInstance();
			$dataFile = $dataFileAdapter->Read($fileId);
			if ($dataFile->isNull())
			{
				return json_encode([
					'status' => 1,
					'message' => 'Data file could not be found.'
				]);
			}

			if ($dataFile->status == DATA_FILE_STATUS_PROCESSING)
			{
				return json_encode([
					'status' => 1,
					'message' => 'Data file is currently being processed. A new task cannot be started until the current process finishes.'
				]);
			}

			$extension = $dataFileAdapter->ReadExtensionById($fileId);
			$pipeline = $pipelineAdapter->Read($pipelineId);

			if ($pipeline->isNull())
			{
				return json_encode([
					'status' => 1,
					'message' => 'Pipeline was not found.'
				]);
			}

			if ($dataFileAdapter->UpdateStatus($fileId, DATA_FILE_STATUS_PROCESSING))
			{
				// Create and a task
				$task = (new TaskFactory())->GetInstanceFromParameters(
					$this->authenticator->GetUserId(),
					TASK_TYPE_FILE_PROCESS,
					0,
					TASK_STATUS_CREATED,
					-1,
					null,
					null,
					null,
					$dataFile->getID(),
					$pipeline->getID()
				);
				$taskAdapter = (new TaskAdapterFactory())->GetInstance();
				$taskId = $taskAdapter->Create($task);

				// Start the task through CLI
				PHPShellHelper::runAsync(getcwd() . "/index.php Task Start $taskId");

				return json_encode([
					'status' => 0,
					'message' => 'Processing started successfully.',
					'task_id' => $taskId
				]);
			}
			else
			{
				return json_encode([
					'status' => 1,
					'message' => 'Failed to update data file status.'
				]);
			}

			return json_encode([
				'status' => 1,
				'message' => 'Unknown error occurred.'
			]);
		}
    }

    public function processFiles()
    {
		if ($this->request->getMethod() == 'post') {
			$fileIds = $this->request->getVar('fileIds');

			$fids = [];
			if (strpos($fileIds, ',')) {
				$fids = explode(',', $fileIds);
			} else {
				$fids[] = intval($fileIds);
			}

			foreach ($fids as $fid) {
				$uploadModel = new Upload();
				$extension = $uploadModel->getFileExtensionById($fid);

				$overwriteFlag = UPLOADER_DELETE_FILE;

				switch (strtolower($extension)) {
					case 'csv':
					case 'xls':
					case 'xlsx':
						$method = 'spreadsheetInsert';
						break;
					case 'phenopacket':
					case 'json':
						$method = 'phenoPacketInsertByFileId';
						break;
					case 'vcf':
						$method = 'vcfInsertByFileId';
						break;
					default:
						return json_encode(0);
				}

				$uploadModel = new Upload();
				$uploadModel->resetFileStatus($fid);

				PHPShellHelper::runAsync(getcwd() . "/index.php Task " . $method . " " . $fid . " " . $overwriteFlag);
			}

			return json_encode(1);
		}
    }

    public function processFilesBySourceId()
	{
		if ($this->request->getMethod() == 'post') {
			$source_id = $this->request->getVar('source_id');
			$pending = $this->request->getVar('pending');
			$overwrite_flag = $this->request->getVar('overwrite');

			PHPShellHelper::runAsync(getcwd() . "/index.php Task insertFilesBySourceId $source_id $pending $overwrite_flag");
			return json_encode(1);
		}
	}

    public function getSourceCounts()
    {
		if ($this->request->getMethod() == 'post') {

			$sourceAdapter = (new SourceAdapterFactory())->GetInstance();
			$sourceList = $sourceAdapter->ReadAllOnline();

			$sc = 0;
			$maxSourcesToDisplay = 12;
			$sourceCountList = [];
			foreach ($sourceList as $source) {
				if ($sc > $maxSourcesToDisplay) {
					break;
				}
				array_push($sourceCountList, $source->record_count);
				$sc++;
			}

			return json_encode($sourceCountList);
		}
    }

	/**
	 * @return false|string|void
	 * @deprecated
	 */
    private function getSourceStatus(){

		if ($this->request->getMethod() == 'post') {
			$source_id = $this->request->getVar('source_id');
			$uploadModel = new Upload();
			$output = ['Files' => [], 'Error' => []];
			$output['Files'] = $uploadModel->getFilesStatusBySourceId($source_id);
			$output['Error'] = $uploadModel->getFileErrorsBySourceId($source_id);

			return json_encode($output);
		}
    }

	public function getOntologyPrefixesAndRelationships()
	{
		$ontology_id = $this->request->getVar('ontology_id');
		$attribute_id = $this->request->getVar('attribute_id');

		$attributeModel = new \App\Models\Attribute();
		$prefixModel = new \App\Models\OntologyPrefix();
		$relationshipModel = new \App\Models\OntologyRelationship();

		$prefixList = $prefixModel->getOntologyPrefixes($ontology_id);
		$relationshipList = $relationshipModel->getOntologyRelationships($ontology_id);

		$prefixes = [];
		$relationships = [];

		foreach ($prefixList as $prefix){
			$prefixes[$prefix['id']] = $prefix['name'];
		}

		foreach ($relationshipList as $relationship) {
			$relationships[$relationship['id']] = $relationship['name'];
		}

		return json_encode([
			'prefixes' =>$prefixes,
			'relationships' => $relationships
		]);
	}

 }
