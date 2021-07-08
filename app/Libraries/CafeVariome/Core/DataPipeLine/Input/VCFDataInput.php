<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Input;

/**
 * Name VCFDataInput.php
 *
 * Created 26/04/2021
 * @author Mehdi Mehtarizadeh
 *
 *
 */

use App\Libraries\CafeVariome\Net\ServiceInterface;

class VCFDataInput extends DataInput
{
    private $headers;
    private $subject_id;
    private $deleted = false;
    private $records;

    public function __construct(int $source_id, int $delete) {
        parent::__construct($source_id);
        $this->delete = $delete;
    }

    public function absorb(int $file_id)
    {
        $this->registerProcess($file_id);

        $vcfFile = $this->uploadModel->getFileById($file_id); //Get a list of files for source
        $this->fileName = $vcfFile[0]['FileName'];
        $this->subject_id = $vcfFile[0]['patient'];

        $this->records = [];

        if ($this->delete == UPLOADER_DELETE_ALL && !$this->deleted) {
            $this->reportProgress($file_id, 0, 1, 'bulkupload', 'Deleting existing data for the source');
            $this->eavModel->deleteRecordsBySourceId($this->sourceId);
            $this->deleted = true;
        }
        else if($this->delete == UPLOADER_DELETE_FILE){
            $this->reportProgress($file_id, 0, 1, 'bulkupload', 'Deleting existing data for the file');
            $this->eavModel->deleteRecordsByFileId($this->sourceId);
        }

        $config = ["AF"];

        if (count ($vcfFile) == 1) {
            while (($line = $this->fileMan->ReadLine($this->fileName)) != false) {
                if (preg_match("/^##/", $line)) {
                    continue;
                }
                // This line has all the headers listed on it
                else if (preg_match("/^#/", $line)) {
                    $line = substr($line, 1);
                    $this->headers = explode("\t", $line);
                }
                // We have reached the data
                else {
                    $values = explode("\t", $line);
                    $uid = md5(uniqid());

                    for ($i=0; $i < 8; $i++) {
                        if ($i == 7) {
                            // go through format column and multidimensional array
                            // having two elements: [0] for alias and [1] for the value
                            $string = $values[$i];
                            $val = array_map(function($string) { return explode('=', $string); }, explode(';', $string));

                            foreach ($val as $v) {
                                if (in_array($v[0], $config)) {
                                    array_push($this->records, ['uid' => $uid, 'attribute' => $v[0], 'value' => $v[1]]);
                                    //$this->createEAV($uid, $this->sourceId, $file_id, $this->subject_id, $v[0], $v[1]);
                                }
                            }
                        }
                        else if ($i == 6) {
                            continue;
                        }
                        else {
                            array_push($this->records, ['uid' => $uid, 'attribute' => $this->headers[$i], 'value' => $values[$i]]);
                            //$this->createEAV($uid, $this->sourceId, $file_id, $this->subject_id, $this->headers[$i], $values[$i]);
                        }
                    }
                }
            }
        }
    }

    public function save(int $file_id)
    {
        $recordCount = count($this->records);
        $recordsProcessed = 0;

        $this->reportProgress($file_id, $recordsProcessed, $recordCount, 'bulkupload', 'Importing data');

		$this->db->commit();

        foreach ($this->records as $record) {
            $this->createEAV($record['uid'], $this->sourceId, $file_id, $this->subject_id, $record['attribute'], $record['value']);
            $this->reportProgress($file_id, $recordsProcessed, $recordCount, 'bulkupload');
            $recordsProcessed ++;
        }

		$this->db->commit();

		if ($this->delete == 1) {
			$this->removeAttribuesAndValuesFiles($this->fileName);
		}

		$this->dumpAttributesAndValues($file_id);
    }
}
