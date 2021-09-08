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

    /**
     * VCF Elastic - Insert into ElasticSearch VCF Files
     * @deprecated
     * Imported from elastic controller by Mehdi Mehtarizadeh (06/08/2019)
     *
     * 08/2019 Removal of mapping types
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/removal-of-types.html
     *
     * @param int $source_id - The source we are inserting into
     * @return N/A
     */
    public function vcfElastic($source_id) {

        $hosts = array($this->setting->settingData['elastic_url']);
        $elasticClient =  \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();

        $uploadModel = new Upload($this->db);
        $elasticModel = new Elastic($this->db);
        $sourceModel = new Source($this->db);

        //Chunk size for bulk indexing in Elasticsearch
        $chunkSize = 100;
        // Get Pending VCF Files
        $vcf = $elasticModel->getvcfPending($source_id);

        $title = $elasticModel->getTitlePrefix();

        for ($t=0; $t < count($vcf); $t++) {
            $index_name = $title."_".$source_id."_".strtolower($vcf[$t]['patient']);
            if ($vcf[$t]['tissue']) {
                $index_name = $index_name."_".strtolower($vcf[$t]['tissue']);
            }

            $params['index'] = $index_name;
            // return;
            if ($elasticClient->indices()->exists($params)){
                $params = ['index' => $index_name];
                $response = $elasticClient->indices()->delete($params);
            }
            $params = [];
            $params['index'] = $index_name;
            $map = '{
                "mappings":{
                    "properties":{
                        "eav_rel":{"type": "join", "relations": {"sub":"eav"}},
                        "type": {"type": "keyword"},
                        "subject_id": {"type": "keyword"},
                        "patient_id": {"type": "keyword"},
                        "file_name": {"type": "keyword"},
                        "source": {"type":"keyword"},
                        "attribute":{"type":"keyword"},
                        "value":{"type":"text", "fields": {"raw":{"type": "keyword"},
                         "d":{"type": "long", "ignore_malformed": "true"},
                         "dt":{"type": "date", "ignore_malformed": "true"}}}
                    }
                }
            }';
            $map2 = json_decode($map,1);
            $params['body'] = $map2;
            //error_log("params: ".var_dump($params));
            $elasticClient->indices()->create($params);
            $source_name = $sourceModel->getSourceNameByID($source_id);

            // Open file for reading
            $handle = fopen(FCPATH."upload/UploadData/".$source_id."/".$vcf[$t]['FileName'], "r");
            // The list of extra parameters we want to include in our insert
            $config = ["AF"];
            $headers = [];
            $counter = 0;

            if ($handle) {
                // Read file line by line
                while (($line = fgets($handle)) !== false) {
                    // Ignore all lines which start with ##
                    if (preg_match("/^##/", $line)) {
                        continue;
                    }
                    // This line has all the headers listed on it
                    else if (preg_match("/^#/", $line)) {
                        $line = substr($line, 1);
                        $headers = explode("\t", $line);
                    }
                    // We have reached the data
                    else {
                        $patient = $vcf[$t]['patient'];
                        // Each row is its own group so we need to create a link id
                        // Explode our lines by tabs
                        $values = explode("\t", $line);
                        $link = md5(uniqid());
                        // create parent document
                        //$bulk['body'][] = ["index"=>["_index"=>$index_name, "_type"=>"subject","_id"=>$link]];
                        //$bulk['body'][] = ["patient_id"=>$patient, "eav_rel"=>["name"=>"sub"], "type"=>"subject", "source"=>$source_name."_vcf"];
                        $bulk['body'][] = ["index"=>["_index"=>$index_name],"_id"=>$link];
                        $bulk['body'][] = ["patient_id"=>$patient, "eav_rel"=>["name"=>"sub"], "type"=>"subject", "source"=>$source_name."_vcf"];

                        //$bulk['body'] = json_encode($bulk_body_head) . "\r\n" . json_encode($bulk_body_tail);

                        $counter++;
                        // Every thousand documents perform a bulk operation to ElasticSearch
                        if ($counter % $chunkSize == 0) {
                            $responses = $elasticClient->bulk($bulk);
                            $bulk=[];
                            unset ($responses);
                        }
                        for ($i=0; $i < 8; $i++) {
                            if ($i == 7) {
                                // go through format column and multidimensional array with each index
                                // having two elements: [0] for alias and [1] for the value
                                $string = $values[$i];
                                $val = array_map(function($string) { return explode('=', $string); }, explode(';', $string));
                                foreach ($val as $v) {
                                    if (in_array($v[0], $config)) {
                                        $id = md5(uniqid());
                                        //$bulk['body'][] = ["index"=>["_index"=>$index_name,"_type"=>"subject", "routing"=>$link,"_id"=>$id]];
                                        //$bulk['body'][] = ["patient_id"=> $patient,"attribute"=>$v[0],"value"=>$v[1], "eav_rel"=>["name"=>"eav","parent"=>$link], "type"=>"eav", "source"=>$source_name."_vcf"];
                                        $bulk['routing'] = $link;
                                        $bulk['body'][] = ["index"=>["_index"=>$index_name],"_id"=>$id];
                                        $bulk['body'][] = ["patient_id"=>$patient, "attribute"=>$v[0],"value"=>$v[1], "eav_rel"=>["name"=>"eav"], "type"=>"eav", "source"=>$source_name."_vcf"];

                                        //$bulk['body'] = json_encode($bulk_body_head) . "\r\n" . json_encode($bulk_body_tail);

                                        $counter++;
                                        if ($counter % $chunkSize == 0) {
                                            error_log($counter);
                                            $responses = $elasticClient->bulk($bulk);
                                            $bulk=[];
                                            unset ($responses);
                                        }
                                    }
                                }
                            }
                            else if ($i == 6) {
                                continue;
                            }
                            else {
                                $id = md5(uniqid());
                                //$bulk['body'][] = ["index"=>["_index"=>$index_name,"_type"=>"subject", "routing"=>$link,"_id"=>$id]];
                                //$bulk['body'][] = ["patient_id"=> $patient,"attribute"=>$headers[$i],"value"=>$values[$i], "eav_rel"=>["name"=>"eav","parent"=>$link], "type"=>"eav", "source"=>$source_name."_vcf"];
                                $bulk['routing'] = $link;
                                $bulk['body'][] = ["index"=>["_index"=>$index_name],"_id"=>$id];
                                $bulk['body'][] = ["patient_id"=>$patient,"attribute"=>$headers[$i],"value"=>$values[$i],"eav_rel"=>["name"=>"eav","parent"=>$link], "type"=>"eav", "source"=>$source_name."_vcf"];

                                //$bulk['body'] = json_encode($bulk_body_head) . "\r\n" . json_encode($bulk_body_tail);

                                $counter++;
                                if ($counter % $chunkSize == 0) {
                                    error_log($counter);
                                    $responses = $elasticClient->bulk($bulk);

                                    $bulk=[];
                                    unset ($responses);
                                }
                            }
                        }
                    }
                }
                fclose($handle);
                    // Finished all files. Send the last records through
                $responses = $elasticClient->bulk($bulk);
                // error_log($counter);
                unset ($responses);
                unset($params);
                $bulk=[];
                $elasticModel->vcfWrap($vcf[$t]['FileName'],$source_id);
            } else {
                // error opening the file.
            }

        }
        error_log("toggling source lock on: ".$source_id);
        $sourceModel->unlockSource($source_id);
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

    /**
     * univUploadInsert - Loop through CSV/XLSX/ODS files with spout to add to eavs table
     *
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
