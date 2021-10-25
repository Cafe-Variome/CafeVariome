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
use CodeIgniter\Controller;
use CodeIgniter\CLI\CLI;
use App\Models\Upload;
use App\Models\Source;
use App\Models\Settings;
use App\Libraries\CafeVariome\Core\DataPipeLine\Input\SpreadsheetDataInput;
use App\Libraries\CafeVariome\Core\DataPipeLine\Input\PhenoPacketDataInput;
use App\Libraries\CafeVariome\Core\DataPipeLine\Input\VCFDataInput;


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

            $inputPipeLine = new SpreadsheetDataInput($sourceId, $overwrite);
            if($inputPipeLine->absorb($fileId)){
				$inputPipeLine->save($fileId);
				$inputPipeLine->finalize($fileId);
			}
			else{
				error_log('There was an issue');
			}
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

	public function IndexDataToElasticsearch(int $source_id, bool $append)
	{
		 $esDataIndex = new ElasticsearchSourceIndex($source_id, $append);
		 $esDataIndex->IndexSource();
	}

	public function IndexDataToNeo4J(int $source_id, bool $append)
	{
		 $n4jDataIndex = new Neo4JSourceIndex($source_id, $append);
		 $n4jDataIndex->IndexSource();
	}

	public function CreateUserInterfaceIndex(int $source_id)
	{
		 $uiDataIndex = new UserInterfaceSourceIndex($source_id);
		 $uiDataIndex->IndexSource();
	}
 }
