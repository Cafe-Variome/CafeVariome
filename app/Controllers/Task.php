<?php namespace App\Controllers;

/**
 * Task.php
 * Created 02/08/2019
 *
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 * Formerly known as sqlinsert.php
 *
 * This is controller is only accessible via the CLI.
 * It implements tasks that need to be run in the background.
 *
 */

use App\Libraries\CafeVariome\CafeVariome;
use App\Libraries\CafeVariome\Core\DataPipeLine\Index\ElasticsearchSourceIndex;
use App\Libraries\CafeVariome\Core\DataPipeLine\Index\Neo4JSourceIndex;
use App\Libraries\CafeVariome\Core\DataPipeLine\Index\UserInterfaceSourceIndex;
use App\Libraries\CafeVariome\Database\IAdapter;
use App\Libraries\CafeVariome\Factory\DataFileAdapterFactory;
use App\Libraries\CafeVariome\Factory\PipelineAdapterFactory;
use App\Libraries\CafeVariome\Factory\SourceAdapterFactory;
use App\Libraries\CafeVariome\Factory\TaskAdapterFactory;
use App\Libraries\CafeVariome\Factory\TaskFactory;
use App\Libraries\CafeVariome\Net\Service\Demon;
use App\Libraries\CafeVariome\Net\ServiceInterface;
use CodeIgniter\Controller;
use App\Libraries\CafeVariome\Core\DataPipeLine\Input\SpreadsheetDataInput;
use App\Libraries\CafeVariome\Core\DataPipeLine\Input\PhenoPacketDataInput;
use App\Libraries\CafeVariome\Core\DataPipeLine\Input\VCFDataInput;


 class Task extends Controller
 {
	 /**
	  * @var IAdapter data adapter layer instance
	  */
	 protected IAdapter $dbAdapter;

	 /**
	  * @var IAdapter Setting adapter instance
	  */
	 protected IAdapter $setting;

	 /**
	  * Constructor
	  *
	  */
	 public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	 {
		 parent::initController($request, $response, $logger);
		 $this->dbAdapter = (new TaskAdapterFactory())->GetInstance();
		 $this->setting =  CafeVariome::Settings();
	 }

	 /**
	  * @param int $task_id
	  * @param bool $batch
	  * @return void
	  */
	 public function Start(int $task_id, bool $batch = false, $final = true)
	 {
		 $task = $this->dbAdapter->Read($task_id);

		 if ($task->isNull())
		 {

		 }
		 else
		 {
			 $task->started = time();
			 $this->dbAdapter->Update($task_id, $task);

			 switch ($task->type)
			 {
				 case TASK_TYPE_FILE_PROCESS:
					 if(is_null($task->data_file_id))
					 {
						 $task->SetError(TASK_ERROR_DATA_FILE_ID_NULL);
						 $task->status = TASK_STATUS_FAILED;
					 }

					 if(is_null($task->pipeline_id))
					 {
						 $task->SetError(TASK_ERROR_PIPELINE_ID_NULL);
						 $task->status = TASK_STATUS_FAILED;
					 }

					 $dataFileAdapter = (new DataFileAdapterFactory())->GetInstance();
					 $pipelineAdapter = (new PipelineAdapterFactory())->GetInstance();

					 $dataFile = $dataFileAdapter->Read($task->data_file_id);
					 if ($dataFile->isNull())
					 {
						 $task->SetError(TASK_ERROR_DATA_FILE_NULL);
						 $task->status = TASK_STATUS_FAILED;
					 }

					 $extension = $dataFileAdapter->ReadExtensionById($task->data_file_id);
					 $pipeline = $pipelineAdapter->Read($task->pipeline_id);

					 if ($pipeline->isNull())
					 {
						 $task->SetError(TASK_ERROR_PIPELINE_NULL);
						 $task->status = TASK_STATUS_FAILED;
					 }

					 $this->dbAdapter->Update($task_id, $task);

					 if ($task->error_code == TASK_ERROR_NO_ERROR)
					 {
						 // Mark task as started
						 $task->status = TASK_STATUS_STARTED;
						 $this->dbAdapter->Update($task_id, $task);

						 $serviceInterface = new ServiceInterface($this->setting->GetInstallationKey());
						 $serviceInterface->RegisterTask($task_id, $batch); // Register task in Demon

						 $inputPipeLine = null;
						 //Start task
						 switch (strtolower($extension))
						 {
							 case 'csv':
							 case 'xls':
							 case 'xlsx':
							 	$inputPipeLine = new SpreadsheetDataInput($task, $dataFile->source_id);
							 	break;
							 case 'phenopacket':
							 case 'json':
								 $inputPipeLine = new PhenoPacketDataInput($task, $dataFile->source_id);
							 	break;
							 case 'vcf':
								 $inputPipeLine = new VCFDataInput($task, $dataFile->source_id);
								 break;
						 }

						 if (!is_null($inputPipeLine))
						 {
							 try
							 {
								 // Mark task as processing
								 $task->status = TASK_STATUS_PROCESSING;
								 $this->dbAdapter->Update($task_id, $task);

								 if($inputPipeLine->Absorb($task->data_file_id))
								 {
									 if ($inputPipeLine->Save($task->data_file_id))
									 {
										 $inputPipeLine->Finalize($task->data_file_id);

										 if ($final)
										 {
											 // (Re-)Create the UI index
											 $inputPipeLine->CreateUIIndex();
										 }

										 // Mark task as finished
										 $task->status = TASK_STATUS_FINISHED;
										 $task->ended = time();
										 $task->progress = 100;
									 }
									 else
									 {
										 // Data was not fully saved for some reason
										 $task->status = TASK_STATUS_FAILED;
										 $task->SetError(TASK_ERROR_DATA_FILE_NOT_SAVED, $inputPipeLine->GetErrorMessage());
									 }
								 }
								 else
								 {
									 // File was not read for some reason
									 $task->status = TASK_STATUS_FAILED;
									 $task->SetError(TASK_ERROR_DATA_FILE_NOT_READ, $inputPipeLine->GetErrorMessage());

								 }
							 }
							 catch(\Exception $ex)
							 {
								 $exceptionMessage = $ex->getMessage();
								 $task->SetError(TASK_ERROR_RUNTIME_ERROR, $exceptionMessage);
							 }
						 }
					 }

					 $this->dbAdapter->Update($task_id, $task);
					 break;
				 case TASK_TYPE_SOURCE_INDEX_ELASTICSEARCH:
				 case TASK_TYPE_SOURCE_INDEX_NEO4J:
				 case TASK_TYPE_SOURCE_INDEX_USER_INTERFACE:

					 if (is_null($task->source_id))
					 {
						 $task->SetError(TASK_ERROR_SOURCE_ID_NULL);
						 $task->status = TASK_STATUS_FAILED;
					 }
					 else
					 {
						 $sourceAdapter = (new SourceAdapterFactory())->GetInstance();

						 $source = $sourceAdapter->Read($task->source_id);

						 if ($source->isNull())
						 {

						 }

						 $this->dbAdapter->Update($task_id, $task);

						 if ($task->error_code == TASK_ERROR_NO_ERROR)
						 {
							 // Mark task as started
							 $task->status = TASK_STATUS_STARTED;
							 $this->dbAdapter->Update($task_id, $task);


							 $serviceInterface = new ServiceInterface($this->setting->GetInstallationKey());
							 $serviceInterface->RegisterTask($task_id); // Register task in Demon
							 try
							 {
								 $indexPipeline = null;
								 switch($task->type)
								 {
									 case TASK_TYPE_SOURCE_INDEX_ELASTICSEARCH:
										 $indexPipeline = new ElasticsearchSourceIndex($task);
										 break;
									 case TASK_TYPE_SOURCE_INDEX_NEO4J:
										 $indexPipeline = new Neo4JSourceIndex($task);
										 break;

									 case TASK_TYPE_SOURCE_INDEX_USER_INTERFACE:
										 $indexPipeline = new UserInterfaceSourceIndex($task);
										 break;
								 }

								 if (!is_null($indexPipeline))
								 {
										 // Mark task as processing
										 $task->status = TASK_STATUS_PROCESSING;
										 $this->dbAdapter->Update($task_id, $task);

										 $indexPipeline->IndexSource();

										 // Mark task as finished
										 $task->status = TASK_STATUS_FINISHED;
										 $task->ended = time();
								 }
							 }
							 catch(\Exception $ex)
							 {
								 $exceptionMessage = $ex->getMessage();
								 $task->SetError(TASK_ERROR_RUNTIME_ERROR, $exceptionMessage);
								 $this->sourceAdapter->Unlock($this->sourceId);
							 }

						 }
					 }

					 $this->dbAdapter->Update($task_id, $task);

				 break;
			 }
		 }
	 }

	 /**
	  * @param string $file_ids
	  * @param int $pipeline_id
	  * @return void
	  */
	 public function CreateBatchTasksForDataFiles(string $file_ids, int $pipeline_id, int $user_id)
	 {
		 $fids = [];
		 if (strpos($file_ids, ','))
		 {
			 $fids = explode(',', $file_ids);
		 }
		 else
		 {
			 $fids[] = intval($file_ids);
		 }

		 $pipelineAdapter = (new PipelineAdapterFactory())->GetInstance();
		 $pipeline = $pipelineAdapter->Read($pipeline_id);

		 if ($pipeline->isNull())
		 {
			 return;
		 }

		 for($c = 0; $c < count($fids); $c++)
		 {
			 $fileId = $fids[$c];
			 $dataFileAdapter = (new DataFileAdapterFactory())->GetInstance();
			 $dataFile = $dataFileAdapter->Read($fileId);
			 if ($dataFile->isNull())
			 {
				 continue;
			 }

			 if ($dataFile->status == DATA_FILE_STATUS_PROCESSING)
			 {
				 continue;
			 }

			 if ($dataFileAdapter->UpdateStatus($fileId, DATA_FILE_STATUS_PROCESSING))
			 {
				 // Create and a task
				 $task = (new TaskFactory())->GetInstanceFromParameters(
					 $user_id,
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

				 $taskId = $this->dbAdapter->Create($task);

				 $this->Start($taskId, true, $c == count($fids) - 1);
			 }
		 }
	 }

	public function StartService()
	{
		$demon = new Demon();
		$demon->Run();
	}
 }
