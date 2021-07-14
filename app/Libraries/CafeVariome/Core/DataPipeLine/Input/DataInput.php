<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Input;

/**
 * Name DataInput.php
 *
 * Created 11/03/2020
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 *
 */

use App\Libraries\CafeVariome\Core\DataPipeLine\Database;
use App\Libraries\CafeVariome\Net\ServiceInterface;
use CodeIgniter\Controller;
use CodeIgniter\CLI\CLI;
use App\Models\Upload;
use App\Models\Source;
use App\Models\Elastic;
use App\Models\Settings;
use App\Models\EAV;
use App\Models\Pipeline;
use App\Libraries\CafeVariome\Core\IO\FileSystem\FileMan;
use App\Libraries\CafeVariome\Core\IO\FileSystem\SysFileMan;
use CodeIgniter\Config;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;


abstract class DataInput
{

    protected $sourceId;

    protected $basePath;
    protected $db;
    protected $fileMan;
    protected $uploadModel;
    protected $sourceModel;
    protected $elasticModel;
    protected $eavModel;
    protected $neo4jModel;
    protected $pipelineModel;
    protected $pipeline_id;
    protected $fileName;
    protected $reader;
	protected $serviceInterface;

	public function __construct(int $source_id)
    {
        $this->sourceId = $source_id;

        $this->basePath = FCPATH . UPLOAD . UPLOAD_DATA . $this->sourceId . DIRECTORY_SEPARATOR;
        $this->db = new Database();

        $this->uploadModel = new Upload();
        $this->sourceModel = new Source();
        $this->eavModel = new EAV();
        $this->pipelineModel = new Pipeline();
        $this->fileMan = new FileMan($this->basePath);
		$this->serviceInterface = new ServiceInterface();

	}

    abstract public function absorb(int $fileId);
    abstract public function save(int $fileId);

    protected function getSourceFiles(int $fileId = -1)
    {
        if ($fileId != -1) {
            return $this->uploadModel->getFiles('FileName, pipeline_id', ['id' => $fileId]);
        }
        else{
            return $this->uploadModel->getFiles('FileName, pipeline_id', ['source_id' => $this->sourceId]);
        }
    }

    public function dumpAttributesAndValues(int $file_id)
    {
        $attributeValueList = $this->eavModel->getUniqueAttributesAndValuesByFileIdAndSourceId($file_id, $this->sourceId);
        $fileName = $this->uploadModel->getFileName($file_id);
        $fileNameWithoutExtension = preg_replace("/\.json|\.phenopacket|\.csv|\.xlsx|\.xls/", '', $fileName);

        $this->fileMan->Write($fileNameWithoutExtension . "_uniq.json", json_encode($attributeValueList));
    }

    public function removeAttribuesAndValuesFiles(string $file_name = null)
    {
        $path = FCPATH . UPLOAD . UPLOAD_DATA . $this->sourceId . DIRECTORY_SEPARATOR;
        $fileMan = new SysFileMan($path);

        if ($file_name != null) {
            $fname = strpos($file_name, '.') ? explode('.', $file_name)[0] : $file_name;
            if ($fileMan->Exists($fname)) {
                $fileMan->Delete($fname);
            }
        }
        else{
            foreach ($fileMan->getFiles() as $file) {
                if (strpos($file, '_uniq.json')) {
                    $fileMan->Delete($file);
                }
            }
        }
    }

	protected function updateSubjectCount()
	{
		$totalRecordCount = $this->sourceModel->countSourceEntries($this->sourceId);
		$this->sourceModel->updateSource(['record_count' => $totalRecordCount], ['source_id' => $this->sourceId]);
    }

	public function finalize(int $file_id, bool $update_subject_count = true)
	{
		if ($update_subject_count){
			$this->updateSubjectCount();
		}
		$this->uploadModel->markEndOfUpload($file_id, $this->sourceId);
		$this->uploadModel->clearErrorForFile($file_id);
		$this->sourceModel->unlockSource($this->sourceId);
		$this->reportProgress($file_id, 1, 1, 'bulkupload', 'Finished', true);
	}


	protected function registerProcess(int $file_id, string $job ='bulkupload', string $message ='Starting')
	{
		$this->serviceInterface->RegisterProcess($file_id, 1, $job, $message);
	}

	protected function reportProgress(int $file_id, int $records_processed, int $total_records, string $job = 'bulkupload', string $status = "", bool $finished = false)
	{
		$this->serviceInterface->ReportProgress($file_id, $records_processed, $total_records, $job, $status, $finished);
	}

	protected function createEAV(string $uid, int $source, int $file_id, string $subject_id, string $attribute, string $value)
	{
		$malicious_chars = ['\\', chr(39), chr(34), '/', '’', '<', '>', '&', ';'];
		$attribute = str_replace($malicious_chars, '', $attribute);
		$value = str_replace($malicious_chars, '', $value);

		$this->db->insert("INSERT IGNORE INTO eavs (uid, source_id, fileName, subject_id, attribute, value) VALUES ('$uid', '$this->sourceId', '$file_id', '$subject_id', '$attribute', '$value');");
	}
}
