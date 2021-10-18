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

use App\Libraries\CafeVariome\Core\DataPipeLine\Index\ElasticsearchDataIndex;
use App\Libraries\CafeVariome\Core\DataPipeLine\Index\Neo4JDataIndex;
use App\Libraries\CafeVariome\Core\DataPipeLine\Index\UserInterfaceDataIndex;
use CodeIgniter\Controller;
use CodeIgniter\CLI\CLI;
use App\Models\Upload;
use App\Models\Source;
use App\Models\Elastic;
use App\Models\Settings;
use App\Models\EAV;
use App\Libraries\CafeVariome\Core\DataPipeLine\Stream\DataStream;
use App\Libraries\CafeVariome\Core\DataPipeLine\Input\SpreadsheetDataInput;
use App\Libraries\CafeVariome\Core\DataPipeLine\Input\PhenoPacketDataInput;
use App\Libraries\CafeVariome\Core\DataPipeLine\Input\VCFDataInput;
use App\Libraries\CafeVariome\Core\DataPipeLine\Input\UniversalDataInput;
use CodeIgniter\Config;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

 class Task extends Controller{

    function __construct(){
        $this->db = \Config\Database::connect();
        $this->setting = Settings::getInstance();
    }

    /**
     * Pheno Packet Insert - manages the loop to insert all recently uploaded json files sequentially
     * into mysql for the given source.
     *
     * @param string $source - Name of source update should be performed for
     * @return N/A
     */
    public function phenoPacketInsertBySourceId(int $source_id, int $overwrite = UPLOADER_DELETE_NONE) {

        $uploadModel = new Upload();
        $sourceModel = new Source();
        $inputPipeLine = new PhenoPacketDataInput($source_id, $overwrite);

        // get a list of json files just uploaded to this source
        $files = $uploadModel->getPhenoPacketFilesBySourceId($source_id, !$overwrite);

        for ($t=0; $t < count($files); $t++) {

            $file = $files[$t]['FileName'];
            $file_id = $files[$t]['ID'];

            try {
                $inputPipeLine->absorb($file_id);
                $inputPipeLine->save($file_id);
				$inputPipeLine->finalize($file_id);
            } catch (\Exception $ex) {
                error_log($ex->getMessage());
            }
        }
    }

        /**
     * Pheno Packet Insert - manages the loop to insert all recently uploaded json files sequentially
     * into mysql for the given source.
     *
     * @param string $source - Name of source update should be performed for
     * @return N/A
     */
    public function phenoPacketInsertByFileId(int $file_id, int $overwrite = UPLOADER_DELETE_FILE) {

        $uploadModel = new Upload();

        $source_id = $uploadModel->getSourceIdByFileId($file_id);

        $inputPipeLine = new PhenoPacketDataInput($source_id, $overwrite);

        try {
            $inputPipeLine->absorb($file_id);
            $inputPipeLine->save($file_id);
			$inputPipeLine->finalize($file_id);
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
        }
    }

    public function vcfInsertBySourceId(int $source_id, int $overwrite = UPLOADER_DELETE_NONE)
    {
        $uploadModel = new Upload();
        $vcfFiles = $uploadModel->getVCFFilesBySourceId($source_id);
        $inputPipeLine = new VCFDataInput($source_id, $overwrite);

        for ($i=0; $i < count($vcfFiles); $i++) {
            $file_id = $vcfFiles[$i]['ID'];

            $inputPipeLine->absorb($file_id);
            $inputPipeLine->save($file_id);
			$inputPipeLine->finalize($file_id);
        }
    }

    public function vcfInsertByFileId(int $file_id, int $overwrite = UPLOADER_DELETE_FILE)
    {
        $uploadModel = new Upload();
        $sourceModel = new Source();

        $source_id = $uploadModel->getSourceIdByFileId($file_id);

        $inputPipeLine = new VCFDataInput($source_id, $overwrite);

        try {
            $inputPipeLine->absorb($file_id);
            $inputPipeLine->save($file_id);
			$inputPipeLine->finalize($file_id);
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
        }
    }

    /**
     * spreadsheetInsert - Loop through CSV/XLSX/ODS files with spout to add to eavs table
     *
     * @param string $file        - The File We are uploading
     * @param int $overwrite         - 0: We do not need to delete data from eavs | 1: We do need to
     * @param string $source      - The name of the source we are uploading to
     * @return array $return_data - Basic information on the status of the upload
     */
    public function spreadsheetInsert(int $fileId,  int $overwrite = UPLOADER_DELETE_FILE) {
        $uploadModel = new Upload();
        $sourceModel = new Source();

        $fileRec = $uploadModel->getFiles('ID, source_id', ['ID' => $fileId]);

        if (count($fileRec) == 1) {
            $sourceId = $fileRec[0]['source_id'];
            if($overwrite){
                $sourceModel->updateSource(['record_count' => 0], ['source_id' => $sourceId]);
            }

            $inputPipeLine = new SpreadsheetDataInput($sourceId, $overwrite);
            $inputPipeLine->absorb($fileId);
            $inputPipeLine->save($fileId);
			$inputPipeLine->finalize($fileId);
        }
        else{
            error_log('File not found.');
        }
    }

    public function insertFilesBySourceId(int $source_id, bool $pending = true, int $overwrite = UPLOADER_DELETE_FILE)
	{
		$uploadModel = new Upload();

		$files = $uploadModel->getFilesBySourceId($source_id, $pending);

		for($c = 0; $c < count($files); $c++)
		{
			if (strpos($files[$c]['FileName'], '.')) {
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
						catch (\Exception $ex) {
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
						catch (\Exception $ex) {
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
						catch (\Exception $ex) {
							error_log($ex->getMessage());
						}
						break;
				}
			}
		}
	}

	 /**
	  * @deprecated
	  */
    public function regenerateElasticsearchAndNeo4JIndex(int $source_id, $add)
    {
        try {
            $dataStream = new DataStream($source_id);
            $dataStream->generateAttributeValueIndex($source_id);
            $dataStream->generateHPOIndex($source_id);
            $dataStream->generateElasticSearchIndex($source_id, $add);
            $dataStream->Neo4JInsert($source_id);
            $dataStream->Finalize($source_id);
        } catch (\Exception $ex) {
            error_log(print_r($ex->getMessage(), 1));
        }
    }

	public function IndexDataToElasticsearch(int $source_id, bool $append)
	{
	 $esDataIndex = new ElasticsearchDataIndex($source_id, $append);
	 $esDataIndex->IndexSource();
	}

	public function IndexDataToNeo4J(int $source_id, bool $append)
	{
	 $n4jDataIndex = new Neo4JDataIndex($source_id, $append);
	 $n4jDataIndex->IndexSource();
	}

	public function CreateUserInterfaceIndex(int $source_id)
	{
	 $uiDataIndex = new UserInterfaceDataIndex($source_id);
	 $uiDataIndex->IndexSource();
	}

    /**
     * univUploadInsert - Loop through CSV/XLSX/ODS files with spout to add to eavs table
     * @deprecated
     * @param string $file        - The File We are uploading
     * @param int $delete         - 0: We do not need to delete data from eavs | 1: We do need to
     * @param string $source      - The name of the source we are uploading to
     * @param string $settingsFile
     * @return array $return_data - Basic information on the status of the upload
     */
    public function univUploadInsert(int $fileId, int $overWrite) {
        $uploadModel = new Upload();
        $fileRec = $uploadModel->getFiles('ID, source_id, setting_file', ['ID' => $fileId]);

        if (count($fileRec) == 1) {
            $sourceId = $fileRec[0]['source_id'];
            $settingFile = $fileRec[0]['setting_file'];

            $inputPipeLine = new UniversalDataInput($sourceId, $overWrite, $settingFile);
            $inputPipeLine->absorb($fileId);
            $inputPipeLine->save($fileId);
        }
        else{
            error_log('File not found.');
        }

    }

 }
