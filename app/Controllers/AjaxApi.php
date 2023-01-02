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
use App\Libraries\CafeVariome\Net\ServiceInterface;
use CodeIgniter\Controller;
use Config\Database;
use App\Libraries\CafeVariome\Net\NetworkInterface;
use App\Libraries\CafeVariome\Net\QueryNetworkInterface;
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

    public function Query()
	{
        $networkInterface = new NetworkInterface();

        //Check to see if user is logged in
        if (!$this->session->has(AUTHENTICATOR_SESSION_NAME))
		{
			return json_encode(['timeout' => 'Your session has timed out. You need to login again.']);
		}

		$authenticatorFactory = new AuthenticatorFactory();
		$authenticator = $authenticatorFactory->GetInstance(
			(new SingleSignOnProviderAdapterFactory())->GetInstance()->Read(
				$this->session->get(AUTHENTICATOR_SESSION_NAME)
			));

		if (!$authenticator->LoggedIn())
		{
			return json_encode(['timeout' => 'Your session has timed out. You need to login again.']);
		}

        $network_key = $this->request->getVar('network_key');
        $queryString = json_encode($this->request->getVar('jsonAPI'));
        $token = $authenticator->GetRefreshToken(['refresh_token' => $this->session->get(SSO_REFRESH_TOKEN_SESSION_NAME)]);

        $user_id = $authenticator->GetUserIdByToken($token);

        try
		{
            $results = [];
            $cafeVariomeQuery = new \App\Libraries\CafeVariome\Query\Compiler();
            $loaclResults = $cafeVariomeQuery->CompileAndRunQuery($queryString, $network_key, $user_id); // Execute locally
            array_push($results, $loaclResults);

            $response = $networkInterface->GetInstallationsByNetworkKey((int)$network_key); // Get other installations within this network
            $installations = [];

            if ($response->status)
			{
                $installations = $response->data;

                foreach ($installations as $installation)
				{
                    if ($installation->installation_key != $this->setting->getInstallationKey())
					{
                        // Send the query
                        $queryNetInterface = new QueryNetworkInterface($installation->base_url);
                        $queryResponse = $queryNetInterface->query($queryString, (int) $network_key, $authenticator->GetBaseURL(), $token);
                        if ($queryResponse->status)
						{
                            array_push($results, json_encode($queryResponse->data));
                        }
                    }
                }
            }

            return json_encode($results);
        }
		catch (\Exception $ex)
		{
            return json_encode(['error' => 'There was a problem executing the query. Please try again with a different query.'.$ex->getMessage()]);
        }
    }

	/**
     * getPhenotypeAttributes
     * @param string network_key
     * @return string in json format, phenotype and hpo data
     *
     */
    public function GetPhenotypeAttributes(int $network_key)
	{
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
	 * @return false|string|void
	 * @throws \Exception
	 */
	 public function IndexDataToElasticsearch()
	 {
		 if ($this->request->getMethod() == 'post')
		 {
			 $source_id = $this->request->getVar('source_id');
			 $overwrite = $this->request->getVar('overwrite') === 'true' ? 1 : 0;

			 if(is_null($source_id))
			 {
				 return json_encode([
					 'status' => 1,
					 'message' => 'Source Id is null.'
				 ]);
			 }

			 $sourceAdapter = (new SourceAdapterFactory())->GetInstance();

			 $source = $sourceAdapter->Read($source_id);

			 if ($source->isNull())
			 {
				 return json_encode([
					 'status' => 1,
					 'message' => 'Source could not be found.'
				 ]);
			 }

			 if ($source->locked)
			 {
				 return json_encode([
					 'status' => 1,
					 'message' => 'Cannot proceed to index data since another operation is being carried out on the source.'
				 ]);
			 }

			 //Lock the source
			 if($sourceAdapter->Lock($source_id))
			 {
				 // Create and a task
				 $task = (new TaskFactory())->GetInstanceFromParameters(
					 $this->authenticator->GetUserId(),
					 TASK_TYPE_SOURCE_INDEX_ELASTICSEARCH,
					 0,
					 TASK_STATUS_CREATED,
					 -1,
					 null,
					 null,
					 null,
					 null,
					 null,
					 $source_id,
					 $overwrite
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
					 'message' => 'Failed to lock the source.'
				 ]);
			 }
		 }
	 }

	/**
	 * @return false|string
	 * @throws \Exception
	 */
	public function IndexDataToNeo4J()
	{
		if ($this->request->getMethod() == 'post') {
			$source_id = $this->request->getVar('source_id');
			$overwrite = $this->request->getVar('overwrite') === 'true' ? 1 : 0;

			if ($this->request->getMethod() == 'post') {
				$source_id = $this->request->getVar('source_id');
				$overwrite = $this->request->getVar('overwrite') === 'true' ? 1 : 0;

				if (is_null($source_id)) {
					return json_encode([
						'status' => 1,
						'message' => 'Source Id is null.'
					]);
				}

				$sourceAdapter = (new SourceAdapterFactory())->GetInstance();

				$source = $sourceAdapter->Read($source_id);

				if ($source->isNull()) {
					return json_encode([
						'status' => 1,
						'message' => 'Source could not be found.'
					]);
				}

				if ($source->locked) {
					return json_encode([
						'status' => 1,
						'message' => 'Cannot proceed to index data since another operation is being carried out on the source.'
					]);
				}

				//Lock the source
				if ($sourceAdapter->Lock($source_id)) {
					// Create and a task
					$task = (new TaskFactory())->GetInstanceFromParameters(
						$this->authenticator->GetUserId(),
						TASK_TYPE_SOURCE_INDEX_NEO4J,
						0,
						TASK_STATUS_CREATED,
						-1,
						null,
						null,
						null,
						null,
						null,
						$source_id,
						$overwrite
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
				} else {
					return json_encode([
						'status' => 1,
						'message' => 'Failed to lock the source.'
					]);
				}
			}
		}
	}

	/**
	 * @return false|string
	 * @throws \Exception
	 */
	public function CreateUserInterfaceIndex()
	{
		if ($this->request->getMethod() == 'post')
		{
			$source_id = $this->request->getVar('source_id');
			$overwrite = $this->request->getVar('overwrite') === 'true' ? 1 : 0;

			if(is_null($source_id))
			{
				return json_encode([
					'status' => 1,
					'message' => 'Source Id is null.'
				]);
			}

			$sourceAdapter = (new SourceAdapterFactory())->GetInstance();

			$source = $sourceAdapter->Read($source_id);

			if ($source->isNull())
			{
				return json_encode([
					'status' => 1,
					'message' => 'Source could not be found.'
				]);
			}

			if ($source->locked)
			{
				return json_encode([
					'status' => 1,
					'message' => 'Cannot proceed to index data since another operation is being carried out on the source.'
				]);
			}

			//Lock the source
			if($sourceAdapter->Lock($source_id)) {
				// Create and a task
				$task = (new TaskFactory())->GetInstanceFromParameters(
					$this->authenticator->GetUserId(),
					TASK_TYPE_SOURCE_INDEX_USER_INTERFACE,
					0,
					TASK_STATUS_CREATED,
					-1,
					null,
					null,
					null,
					null,
					null,
					$source_id,
					$overwrite
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
					'message' => 'Failed to lock the source.'
				]);
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
					$pipeline->getID(),
					$dataFile->source_id
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
		}
    }

	public function ShutdownService()
	{
		if ($this->request->getMethod() == 'post')
		{
			$serviceInterface = new ServiceInterface();

			if(!$serviceInterface->ping())
			{
				// Service not running
				return json_encode([
				'status' => 1,
				'message' => 'Service is not running.'
			]);

			}

			$serviceInterface->Shutdown();

			sleep(5);

			if(!$serviceInterface->ping())
			{
				// Shutdown was successful;
				return json_encode([
					'status' => 0,
					'message' => 'Service shutdown was successful.'
				]);
			}
			else
			{
				// Shutdown was not successful;
				return json_encode([
					'status' => 1,
					'message' => 'Service shutdown was not successful.'
				]);
			}
		}
	}

	public function StartService()
	{
		if ($this->request->getMethod() == 'post')
		{
			$serviceInterface = new ServiceInterface();

			if($serviceInterface->ping())
			{
				// Service is running
				return json_encode([
					'status' => 1,
					'message' => 'Service is already running.'
				]);

			}

			$serviceInterface->Start();

			sleep(5);

			if($serviceInterface->ping())
			{
				// Shutdown was successful;
				return json_encode([
					'status' => 0,
					'message' => 'Service has started.'
				]);
			}
			else
			{
				// Shutdown was not successful;
				return json_encode([
					'status' => 1,
					'message' => 'Service cannot be started.'
				]);
			}
		}
	}

	/**
	 * @return false|string|void
	 * @throws \Exception
	 */
    public function ProcessFiles()
    {
		if ($this->request->getMethod() == 'post')
		{
			$fileIds = $this->request->getVar('fileIds');
			$source_id = $this->request->getVar('sourceId');

			if ($fileIds == '' || $fileIds == null)
			{
				$dataFileAdapter = (new DataFileAdapterFactory())->GetInstance();
				$fileIds = $dataFileAdapter->ReadUploadedAndImportedIdsBySourceId($source_id);
				if (count($fileIds) > 0)
				{
					$fileIds = implode(',', $fileIds);
				}
				else
				{
					return json_encode([
						'status' => 1,
						'message' => 'No file Ids given.'
					]);
				}
			}

			$pipelineId = $this->request->getVar('pipelineId');
			$userId = $this->authenticator->GetUserId();
			PHPShellHelper::runAsync(getcwd() . "/index.php Task CreateBatchTasksForDataFiles $fileIds $pipelineId $userId");

			sleep(1);

			return json_encode([
				'status' => 0,
				'message' => 'Processing started successfully.'
			]);
		}
    }

	/**
	 * @return false|string|void
	 */
	public function CountUploadedAndImportedFiles()
	{
		if ($this->request->getMethod() == 'post')
		{
			try
			{
				$source_id = $this->request->getVar('sourceId');
				$dataFileAdapter = (new DataFileAdapterFactory())->GetInstance();
				$count = $dataFileAdapter->CountUploadedAndImportedBySourceId($source_id);

				return json_encode([
					'status' => 0,
					'count' => $count
				]);
			}
			catch(\Exception $ex)
			{
				return json_encode([
					'status' => 1,
					'count' => -1,
					'error' => $ex->getMessage()
				]);
			}
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

	public function getOntologyPrefixesAndRelationships()
	{
		$ontology_id = $this->request->getVar('ontology_id');

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
