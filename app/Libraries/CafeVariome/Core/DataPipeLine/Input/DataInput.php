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
use App\Models\OntologyPrefix;
use App\Models\Upload;
use App\Models\Source;
use App\Models\EAV;
use App\Models\Pipeline;
use App\Models\Attribute;
use App\Libraries\CafeVariome\Core\IO\FileSystem\FileMan;
use App\Libraries\CafeVariome\Core\IO\FileSystem\SysFileMan;
use App\Models\Value;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;


abstract class DataInput
{

    protected $sourceId;

    protected $basePath;
	protected array $configuration;
	protected $db;
    protected $fileMan;
    protected $uploadModel;
    protected $sourceModel;
    protected $elasticModel;
    protected $eavModel;
    protected $pipelineModel;
    protected $pipeline_id;
    protected $fileName;
    protected $reader;
	protected $serviceInterface;
	protected Attribute $attributeModel;
	protected Value $valueModel;
	protected array $attributes;
	private OntologyPrefix $ontologyPrefixModel;

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
		$this->attributeModel = new Attribute();
		$this->valueModel = new Value();
		$this->attributes = [];
		$this->configuration = [];
		$this->ontologyPrefixModel = new OntologyPrefix();
	}

    abstract public function absorb(int $fileId);
    abstract public function save(int $fileId);

	abstract protected function initializeConfiguration();

	protected function getSourceFiles(int $fileId = -1)
    {
        if ($fileId != -1) {
            return $this->uploadModel->getFiles('FileName, pipeline_id', ['id' => $fileId]);
        }
        else{
            return $this->uploadModel->getFiles('FileName, pipeline_id', ['source_id' => $this->sourceId]);
        }
    }

	/**
	 * @deprecated
	 */
    public function dumpAttributesAndValues(int $file_id)
    {
        $attributeValueList = $this->eavModel->getUniqueAttributesAndValuesByFileIdAndSourceId($file_id, $this->sourceId);
        $fileName = $this->uploadModel->getFileName($file_id);
        $fileNameWithoutExtension = preg_replace("/\.json|\.phenopacket|\.csv|\.xlsx|\.xls/", '', $fileName);

        $this->fileMan->Write($fileNameWithoutExtension . "_uniq.json", json_encode($attributeValueList));
    }

	/**
	 * @deprecated
	 */
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

	protected function createEAV(string $uid, int $file_id, string $subject_id, int $attribute_id, int $value_id)
	{
		$this->db->insert("INSERT IGNORE INTO eavs (uid, source_id, file_id, subject_id, attribute_id, value_id) VALUES ('$uid', '$this->sourceId', '$file_id', '$subject_id', '$attribute_id', '$value_id');");
	}

	protected function createAttribute(string $name): int
	{
		$attribute_id = $this->attributeModel->getAttributeIdByNameAndSourceId($name, $this->sourceId);
		if ($attribute_id == -1){
			$attribute_id = $this->attributeModel->createAttribute($name, $this->sourceId, $name);
		}

		return $attribute_id;
	}

	protected function createValue(string $value, int $attribute_id): int
	{
		$value_id = $this->valueModel->getValueIdByNameAndAttributeId($value, $attribute_id);
		if ($value_id == -1){
			$value_id = $this->valueModel->createValue($value, $attribute_id, $value);
		}

		return $value_id;
	}

	protected function sanitiseString(string $dirty_string, string $soap = ''): string
	{
		$malicious_chars = ['\\', chr(39), chr(34), '/', '’', '<', '>', '&', ';'];
		return htmlentities(str_replace($malicious_chars, $soap, $dirty_string));
	}

	protected function getAttributeIdByName(string $attribute): int
	{
		if (array_key_exists($attribute, $this->attributes))
		{
			$attribute_id = $this->attributes[$attribute]['id'];
		}
		else
		{
			$attribute_id = $this->createAttribute($attribute); // Insert attribute to database
			// Add attribute to the list
			$this->attributes[$attribute] = [
				'id' => $attribute_id,
				'type' => ATTRIBUTE_TYPE_UNDEFINED,
				'values' => []
			];
		}
		return $attribute_id;
	}

	protected function getValueIdByNameAndAttributeId(string $value, string $attribute): int
	{
		if (array_key_exists($value, $this->attributes[$attribute]['values']))
		{
			$value_id = $this->attributes[$attribute]['values'][$value]['id'];
			$this->incrementValueFrequency($value, $attribute); // increment value frequency
		}
		else
		{
			$value_id = $this->createValue($value, $this->attributes[$attribute]['id']); // Insert value to database
			// Add value to the list
			$this->attributes[$attribute]['values'][$value] = [
				'id' => $value_id,
				'frequency' => 1
			];
		}
		return $value_id;
	}

	protected function getValueByValueIdAndAttributeName(int $value_id, string $attribute): ?string
	{
		if (array_key_exists($attribute, $this->attributes))
		{
			$values = $this->attributes[$attribute]['values'];

			foreach ($values as $value => $value_details){
				if ($value_details['id'] === $value_id){
					return $value;
				}
			}
			return null;
		}
		throw new \Exception('Attribute does not exist');
	}

	protected function incrementValueFrequency(string $value, string $attribute): void
	{
		$freq = $this->attributes[$attribute]['values'][$value]['frequency'];
		$this->attributes[$attribute]['values'][$value]['frequency'] = $freq + 1;
	}

	protected function updateValueFrequencies()
	{
		$db = \Config\Database::connect();
		$db->transStart();

		foreach ($this->attributes as $attribute => $attribute_details){
			foreach ($this->attributes[$attribute]['values'] as $value => $value_details){
				$this->valueModel->updateFrequency($value_details['id'], $this->attributes[$attribute]['values'][$value]['frequency']);
			}
		}

		$db->transComplete();
	}

	protected function deleteExistingRecords(int $file_id)
	{
		$db = \Config\Database::connect();
		$db->transStart();
		$valueFrequencies = $this->eavModel->getValueFrequenciesBySourceIdAndFileId($this->sourceId, $file_id); // Get value frequencies
		if (count($valueFrequencies) > 0){
			$this->eavModel->deleteRecordsByFileId($file_id); // Delete records from eavs
			$valueCount = count($valueFrequencies);
			for ($i = 0; $i < $valueCount; $i++){
				$value_id = $valueFrequencies[$i]['value_id'];
				$value_frequency = $valueFrequencies[$i]['frequency'];
				$this->valueModel->updateFrequency($value_id, -$value_frequency);
				$this->valueModel->deleteAbsentValue($value_id); // Delete value if frequency is 0
				unset($valueFrequencies[$i]);
			}
			unset($value_id);
		}
		$db->transComplete();
	}

	protected function determineAttributesType()
	{
		foreach ($this->attributes as $attribute => $attribute_details){
			$attribute_id = $attribute_details['id'];
			$attribute_type = $this->attributeModel->getAttributeType($attribute_id);

			if ($attribute_type == ATTRIBUTE_TYPE_STRING && $this->valueModel->countValuesByAttributeId($attribute_id) > 0)
			{
				$this->attributes[$attribute]['type'] = $attribute_type;
				continue; // If attribute has already values that are string, then skip and go to the next value.
			}

			$assumed_type = count($this->attributes[$attribute]['values']) > 0 ? ATTRIBUTE_TYPE_NUMERIC_NATURAL : $attribute_type; //Start with natural number and switch to other types if any instance is found

			$c = 0;
			$minMaxArray = $this->attributeModel->getAttributeMinimumAndMaximum($attribute_id);
			if (count($minMaxArray) == 2){
				$minimum_value = $minMaxArray[0];
				$maximum_value = $minMaxArray[1];
				$c++; // to skip the initialization in the below loop
			}

			foreach ($this->attributes[$attribute]['values'] as $value => $value_details){
				if ($c == 0){
					//preset minimum and maximum
					$minimum_value = $value;
					$maximum_value = $value;
				}

				if ($value == intval($value)) $value = intval($value);
				else if ($value == floatval($value)) $value = floatval($value);

				if (is_numeric($value)){
					if (is_integer($value) && $value < 0){
						$assumed_type = ATRRIBUTE_TYPE_NUMERIC_INTEGER;
					}
					elseif (is_float($value)){
						$assumed_type = ATRRIBUTE_TYPE_NUMERIC_REAL;
					}
					if ($value > $maximum_value) $maximum_value = $value;
					if ($value < $minimum_value) $minimum_value = $value;
				}
				elseif (is_string($value)){
					$assumed_type = ATRRIBUTE_TYPE_STRING;
					$minimum_value = null;
					$maximum_value = null;
					break;
				}
				$c++;
			}
			$this->attributes[$attribute]['type'] = $assumed_type;
			$this->attributeModel->setAttributeType($attribute_id,  $assumed_type);
			if (is_numeric($minimum_value) && is_numeric($maximum_value)){
				$this->attributeModel->setAttributeMinimumAndMaximum($attribute_id, $minimum_value, $maximum_value);
			}
		}
	}

	protected function determineAttributesStorageLocation()
	{
		$db = \Config\Database::connect();
		$db->transStart();
		foreach ($this->attributes as $attribute => $attribute_details) {
			// If attribute exists in HPO, Negated HPO, ORPHA values of the pipeline, then it is stored in Neo4J
			// Otherwise it is stored on Elasticsearch
			$attribute_id = $attribute_details['id'];
			if ($attribute_details['type'] == ATTRIBUTE_TYPE_ONTOLOGY_TERM){
				$this->attributeModel->setAttributeStorageLocation($attribute_id, ATTRIBUTE_STORAGE_NEO4J);
			}
			else{
				$this->attributeModel->setAttributeStorageLocation($attribute_id, ATTRIBUTE_STORAGE_ELASTICSEARCH);
			}
		}
		$db->transComplete();
	}

	private function valueStartsWithOntologyPrefix(string $value, array $prefixes): bool
	{
		for ($i = 0; $i < count($prefixes); $i++){
			if (str_starts_with($value, $prefixes[$i])){
				return true;
			}
		}

		return false;
	}
}
