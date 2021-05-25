<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Input;

/**
 * Name DataInput.php
 * 
 * Created 11/03/2020
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 * 
 */

use CodeIgniter\Controller;
use CodeIgniter\CLI\CLI;
use App\Models\Upload;
use App\Models\Source;
use App\Models\Elastic;
use App\Models\Settings;
use App\Models\EAV;
use App\Models\Neo4j;
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

    public function __construct(int $source_id)
    {
        $this->sourceId = $source_id;

        $this->basePath = FCPATH . UPLOAD . UPLOAD_DATA . $this->sourceId . DIRECTORY_SEPARATOR;
        $this->db = \Config\Database::connect();

        $this->uploadModel = new Upload();
        $this->sourceModel = new Source();
        $this->eavModel = new EAV();
        $this->pipelineModel = new Pipeline();
        $this->fileMan = new FileMan($this->basePath);
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

    public function removeAttribuesAndValuesFiles()
    {
        $path = FCPATH . UPLOAD . UPLOAD_DATA . $this->sourceId . DIRECTORY_SEPARATOR;
        $fileMan = new SysFileMan($path);

        foreach ($fileMan->getFiles() as $file) {
            if (strpos($file, '_uniq.json')) {
                $fileMan->Delete($file);
            }
        }
    }

}
