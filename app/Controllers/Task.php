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

use App\Libraries\CafeVariome\Core\DataPipeLine\Index\ElasticsearchSourceIndex;
use App\Libraries\CafeVariome\Core\DataPipeLine\Index\Neo4JSourceIndex;
use App\Libraries\CafeVariome\Core\DataPipeLine\Index\UserInterfaceSourceIndex;
use App\Libraries\CafeVariome\Factory\DataFileAdapterFactory;
use App\Libraries\CafeVariome\Factory\PipelineAdapterFactory;
use App\Libraries\CafeVariome\Factory\TaskAdapterFactory;
use App\Libraries\CafeVariome\Net\Service\Demon;
use App\Libraries\CafeVariome\Net\ServiceInterface;
use CodeIgniter\Controller;
use CodeIgniter\CLI\CLI;
use App\Models\Upload;
use App\Models\Source;
use App\Models\Settings;
use App\Libraries\CafeVariome\Core\DataPipeLine\Input\SpreadsheetDataInput;
use App\Libraries\CafeVariome\Core\DataPipeLine\Input\PhenoPacketDataInput;
use App\Libraries\CafeVariome\Core\DataPipeLine\Input\VCFDataInput;


 class Task extends Controller
 {
	 protected $dbAdapter;


	 /**
	  * Constructor
	  *
	  */
	 public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	 {
		 parent::initController($request, $response, $logger);

		 $this->dbAdapter = (new TaskAdapterFactory())->GetInstance();
	 }

	 public function Start(int $task_id)
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

//					 if ($dataFile->status == DATA_FILE_STATUS_PROCESSING)
//					 {
//						 $task->SetError(TASK_ERROR_DUPLICATE);
//						 $task->status = TASK_STATUS_CENCELLED;
//					 }

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

						 $serviceInterface = new ServiceInterface();
						 $serviceInterface->RegisterTask($task_id); // Register task in Demon

						 $overwrite = UPLOADER_DELETE_FILE;
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

								 $inputPipeLine->Absorb($task->data_file_id);
								 $inputPipeLine->Save($task->data_file_id);
								 $inputPipeLine->Finalize($task->data_file_id);

								 // Mark task as finished
								 $task->status = TASK_STATUS_FINISHED;
								 $task->ended = time();
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
				 case TASK_TYPE_SOURCE_INDEX:
					 break;
			 }
		 }
	 }

	 /**
	  * Reads phenopacket files uploaded/imported for a specific source and insert their data into mysql.
	  * @param int $source_id - Id of the source
	  * @param int $overwrite Whether to overwrite data of the files or not.
	  * @return void
	  */
    public function phenoPacketInsertBySourceId(int $source_id, int $overwrite = UPLOADER_DELETE_NONE)
	{
        $uploadModel = new Upload();
        $inputPipeLine = new PhenoPacketDataInput($source_id, $overwrite);

        // get a list of json files just uploaded to this source
        $files = $uploadModel->getPhenoPacketFilesBySourceId($source_id, !$overwrite);

        for ($t=0; $t < count($files); $t++)
		{
            $file_id = $files[$t]['ID'];
            try
			{
                $inputPipeLine->absorb($file_id);
                $inputPipeLine->save($file_id);
				$inputPipeLine->finalize($file_id);
            }
			catch (\Exception $ex)
			{
                error_log($ex->getMessage());
            }
        }
    }

	/**
     * Reads a single phenopacket file and inserts its data into mysql.
     * @param int $file_id - Id of the file uploaded or inserted.
     * @param int $overwrite - Whether to overwrite data of the file or not.
     * @return void
     */
    public function phenoPacketInsertByFileId(int $file_id, int $overwrite = UPLOADER_DELETE_FILE)
	{
        $uploadModel = new Upload();
        $source_id = $uploadModel->getSourceIdByFileId($file_id);
        $inputPipeLine = new PhenoPacketDataInput($source_id, $overwrite);

        try
		{
            $inputPipeLine->absorb($file_id);
            $inputPipeLine->save($file_id);
			$inputPipeLine->finalize($file_id);
        }
		catch (\Exception $ex)
		{
            error_log($ex->getMessage());
        }
    }

	 /**
	  * Reads VCF files uploaded/imported for a specific source and insert their data into mysql.
	  * @param int $source_id - Id of the source
	  * @param int $overwrite Whether to overwrite data of the files or not.
	  * @return void
	  */
    public function vcfInsertBySourceId(int $source_id, int $overwrite = UPLOADER_DELETE_NONE)
    {
        $uploadModel = new Upload();
        $vcfFiles = $uploadModel->getVCFFilesBySourceId($source_id);
        $inputPipeLine = new VCFDataInput($source_id, $overwrite);

        for ($i=0; $i < count($vcfFiles); $i++)
		{
            $file_id = $vcfFiles[$i]['ID'];
            $inputPipeLine->absorb($file_id);
            $inputPipeLine->save($file_id);
			$inputPipeLine->finalize($file_id);
        }
    }

	 /**
	  * Reads a single VCF file and inserts its data into mysql.
	  * @param int $file_id - Id of the file uploaded or inserted.
	  * @param int $overwrite - Whether to overwrite data of the file or not.
	  * @return void
	  */
    public function vcfInsertByFileId(int $file_id, int $overwrite = UPLOADER_DELETE_FILE)
    {
        $uploadModel = new Upload();
        $source_id = $uploadModel->getSourceIdByFileId($file_id);
        $inputPipeLine = new VCFDataInput($source_id, $overwrite);

        try
		{
            $inputPipeLine->absorb($file_id);
            $inputPipeLine->save($file_id);
			$inputPipeLine->finalize($file_id);
        }
		catch (\Exception $ex)
		{
            error_log($ex->getMessage());
        }
    }

	 /**
	  * Reads a single spreadsheet file and inserts its data into mysql.
	  * @param int $file_id - Id of the file uploaded or inserted.
	  * @param int $overwrite - Whether to overwrite data of the file or not.
	  * @return void
	  */
    public function spreadsheetInsert(int $fileId,  int $overwrite = UPLOADER_DELETE_FILE)
	{
        $uploadModel = new Upload();
        $fileRec = $uploadModel->getFiles('ID, source_id', ['ID' => $fileId]);

        if (count($fileRec) == 1)
		{
            $sourceId = $fileRec[0]['source_id'];
            $inputPipeLine = new SpreadsheetDataInput($sourceId, $overwrite);
            if($inputPipeLine->absorb($fileId))
			{
				$inputPipeLine->save($fileId);
				$inputPipeLine->finalize($fileId);
			}
			else
			{
				error_log('There was an issue');
			}
        }
        else{
            error_log('File not found.');
        }
    }

	 /** Reads all the files uploaded/imported to a source and inserts their data into mysql.
	  * @param int $source_id - Id of the source
	  * @param bool $pending - Whether to read pending files only or not.
	  * @param int $overwrite - Whether to overwrite data of the file or not.
	  * @return void
	  */
    public function insertFilesBySourceId(int $source_id, bool $pending = true, int $overwrite = UPLOADER_DELETE_FILE)
	{
		$uploadModel = new Upload();

		$files = $uploadModel->getFilesBySourceId($source_id, $pending);

		for($c = 0; $c < count($files); $c++)
		{
			if (strpos($files[$c]['FileName'], '.'))
			{
				$file_id = $files[$c]['ID'];
				$file_name_array = explode('.', $files[$c]['FileName']);
				$extension = $file_name_array[count($file_name_array) - 1];
				$final_round = $c == (count($files) - 1);

				switch (strtolower($extension))
				{
					case 'vcf':
						try
						{
							$inputPipeLine = new VCFDataInput($source_id, $overwrite);
							$inputPipeLine->absorb($file_id);
							$inputPipeLine->save($file_id);
							$inputPipeLine->finalize($file_id, $final_round);
							unset($inputPipeLine);
						}
						catch (\Exception $ex)
						{
							error_log($ex->getMessage());
						}
						break;
					case 'json':
					case 'phenopacket':
						try
						{
							$inputPipeLine = new PhenoPacketDataInput($source_id, $overwrite);
							$inputPipeLine->absorb($file_id);
							$inputPipeLine->save($file_id);
							$inputPipeLine->finalize($file_id, $final_round);
							unset($inputPipeLine);
						}
						catch (\Exception $ex)
						{
							error_log($ex->getMessage());
						}
						break;
					case 'csv':
					case 'xls':
					case 'xlsx':
						try
						{
							$inputPipeLine = new SpreadsheetDataInput($source_id, $overwrite);
							$inputPipeLine->absorb($file_id);
							$inputPipeLine->save($file_id);
							$inputPipeLine->finalize($file_id, $final_round);
							unset($inputPipeLine);
						}
						catch (\Exception $ex)
						{
							error_log($ex->getMessage());
						}
						break;
				}
			}
		}
	}

	 /**
	  * Indexes data of the specified source to Elasticsearch
	  * @param int $source_id - Id of the source
	  * @param bool $append - Whether to append unindexed data or re-index all records.
	  * @return void
	  */
	public function IndexDataToElasticsearch(int $source_id, bool $append)
	{
		 $esDataIndex = new ElasticsearchSourceIndex($source_id, $append);
		 $esDataIndex->IndexSource();
	}

	 /**
	  * Indexes data of the specified source to Neo4J
	  * @param int $source_id - Id of the source
	  * @param bool $append - Whether to append unindexed data or re-index all records.
	  * @return void
	  */
	public function IndexDataToNeo4J(int $source_id, bool $append)
	{
		 $n4jDataIndex = new Neo4JSourceIndex($source_id, $append);
		 $n4jDataIndex->IndexSource();
	}

	 /**
	  * Creates the user interface index for the specified source.
	  * @param int $source_id - Id of the source
	  * @return void
	  */
	public function CreateUserInterfaceIndex(int $source_id)
	{
		 $uiDataIndex = new UserInterfaceSourceIndex($source_id);
		 $uiDataIndex->IndexSource();
	}

	public function StartService()
	{
		$demon = new Demon();
		$demon->Run();
	}
 }
