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
use App\Libraries\CafeVariome\Core\DataPipeLine\DataPipeLine;
use App\Libraries\CafeVariome\Entities\Task;
use App\Libraries\CafeVariome\Factory\AttributeFactory;
use App\Libraries\CafeVariome\Factory\GroupFactory;
use App\Libraries\CafeVariome\Factory\SubjectFactory;
use App\Libraries\CafeVariome\Factory\ValueFactory;
use App\Libraries\CafeVariome\Net\ServiceInterface;
use App\Models\OntologyPrefix;

abstract class DataInput extends DataPipeLine
{
	protected $db;
    protected $fileMan;
    protected $pipeline_id;
    protected $fileName;
    protected $reader;
	protected $serviceInterface;
	protected array $attributes;
	protected array $subjects;
	protected array $groups;
	private OntologyPrefix $ontologyPrefixModel;

	public function __construct(Task $task, int $source_id)
    {
		parent::__construct($source_id);
		$this->pipelineId = $task->pipeline_id;
		$this->overwrite = $task->overwrite;
		$this->taskId = $task->getID();
		$this->continue = true;

        $this->db = new Database();

		$this->serviceInterface = new ServiceInterface();
		$this->attributes = [];
		$this->subjects = [];
		$this->groups = [];
		$this->configuration = [];
		$this->ontologyPrefixModel = new OntologyPrefix();
	}

    abstract public function Absorb(int $fileId): bool;
    abstract public function Save(int $fileId): bool;

	abstract protected function InitializePipeline();

	protected function updateSubjectCount()
	{
		$totalRecordCount = $this->subjectAdapter->CountBySourceId($this->sourceId);
		$this->sourceAdapter->UpdateRecordCount($this->sourceId, $totalRecordCount);
    }

	public function Finalize(int $file_id, bool $update_subject_count = true)
	{
		if ($update_subject_count)
		{
			$this->updateSubjectCount();
		}

		$this->dataFileAdapter->UpdateRecordCount($file_id, count($this->subjects));
		$this->dataFileAdapter->UpdateStatus($file_id, DATA_FILE_STATUS_PROCESSED);

		$this->sourceAdapter->Unlock($this->sourceId);
		$this->ReportProgress(100, 'Finished', true);

		// (Re-)Create the UI index
//		$uiDataIndex = new UserInterfaceSourceIndex($this->sourceId);
//		$uiDataIndex->IndexSource();
	}

	protected function ReportProgress(int $progress, string $status = '', bool $finished = false)
	{
		$response = $this->serviceInterface->ReportProgress($this->taskId, $progress, $status, $finished);

		if ($response['response_received'])
		{
			$payload = $response['payload'];
			if (is_array($payload) && count($payload) == 1)
			{
				foreach ($payload as $taskId => $value)
				{
					$this->continue = $value['continue'];
				}
			}
		}
	}

	protected function createEAV(int $group_id, int $file_id, int $subject_id, int $attribute_id, int $value_id)
	{
		$this->db->insert("INSERT IGNORE INTO eavs (group_id, source_id, file_id, subject_id, attribute_id, value_id) VALUES ('$group_id', '$this->sourceId', '$file_id', '$subject_id', '$attribute_id', '$value_id');");
	}

	protected function createSubject(string $name): int
	{
		$subject_id = $this->subjectAdapter->ReadIdByNameAndSourceId($name, $this->sourceId);
		if (is_null($subject_id))
		{
			$subject_id = $this->subjectAdapter->Create(
				(new SubjectFactory())->GetInstanceFromParameters($name, $this->sourceId, $name)
			);
		}

		return $subject_id;
	}

	protected function createGroup(string $name): int
	{
		$group_id = $this->groupAdapter->ReadIdByNameAndSourceId($name, $this->sourceId);
		if (is_null($group_id))
		{
			$group_id = $this->groupAdapter->Create(
				(new GroupFactory())->GetInstanceFromParameters($name, $this->sourceId, $name)
			);
		}

		return $group_id;
	}

	protected function createAttribute(string $name): int
	{
		$attribute_id = $this->attributeAdapter->ReadIdByNameAndSourceId($name, $this->sourceId);
		if (is_null($attribute_id))
		{
			$attribute_id = $this->attributeAdapter->Create(
				(new AttributeFactory())->GetInstanceFromParameters($name, $this->sourceId, $name)
			);
		}

		return $attribute_id;
	}

	protected function createValue(string $name, int $attribute_id): int
	{
		$value_id = $this->valueAdapter->ReadIdByNameAndAttributeId($name, $attribute_id);
		if (is_null($value_id))
		{
			$value_id = $this->valueAdapter->Create(
				(new ValueFactory())->GetInstanceFromParameters($name, $attribute_id, $name)
			);
		}

		return $value_id;
	}

	protected function sanitiseString(string $dirty_string, string $soap = ''): string
	{
		$malicious_chars = ['\\', chr(39), chr(34), '/', '’', '<', '>', '&', ';'];

		if(
			str_starts_with($dirty_string, 'http://') ||
			str_starts_with($dirty_string, 'https://') ||
			str_starts_with($dirty_string, 'ftp://') ||
			str_starts_with($dirty_string, 'ntp://')
		)
		{
			unset($malicious_chars[3]); // Remove / (slash) from $malicious_chars not to damage the URL
		}
		return htmlentities(str_replace($malicious_chars, $soap, $dirty_string));
	}

	protected function getSubjectIdByName(string $subject): int
	{
		$subject = trim(strtolower(preg_replace('/\s+/', '_', $subject))); // replace spaces with underline
		$subject = $this->sanitiseString($subject);

		if (array_key_exists($subject, $this->subjects))
		{
			$subject_id = $this->subjects[$subject]['id'];
		}
		else
		{
			$subject_id = $this->createSubject($subject); // Insert subject to database
			// Add attribute to the list
			$this->subjects[$subject] = [
				'id' => $subject_id
			];
		}
		return $subject_id;
	}

	protected function getGroupIdByName(string $group): int
	{
		$group = trim(strtolower(preg_replace('/\s+/', '_', $group))); // replace spaces with underline
		$group = $this->sanitiseString($group);

		if (array_key_exists($group, $this->groups))
		{
			$group_id = $this->groups[$group]['id'];
		}
		else
		{
			$group_id = $this->createGroup($group); // Insert subject to database
			$attribute_ids = $this->groupAdapter->ReadAttributeIds($group_id);
			// Add attribute to the list
			$this->groups[$group] = [
				'id' => $group_id,
				'attribute_ids' => $attribute_ids
			];
		}
		return $group_id;
	}

	protected function getAttributeIdByName(string $attribute): int
	{
		$attribute = trim(strtolower(preg_replace('/\s+/', '_', $attribute))); // replace spaces with underline
		$attribute = $this->sanitiseString($attribute); // sanitise attribute here to remove malicious characters

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
		$attribute = trim(strtolower(preg_replace('/\s+/', '_', $attribute))); // replace spaces with underline
		$attribute = $this->sanitiseString($attribute); // sanitise attribute here to remove malicious characters

		$value = strtolower($value);
		$value = $this->sanitiseString($value);

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

	protected function associateGroupWithAttributes(string $group_name, array $attribute_ids)
	{
		$group = $this->groups[$group_name];
		$group_attributes = $group['attribute_ids'];
		$attribute_ids_to_add = [];
		for($c = 0; $c < count($attribute_ids); $c++)
		{
			if (!in_array($attribute_ids[$c], $group_attributes))
			{
				array_push($attribute_ids_to_add, $attribute_ids[$c]);
			}
		}

		$this->groupAdapter->AddAttributes($group['id'], $attribute_ids_to_add);

		$group['attribute_ids'] = array_merge($group_attributes, $attribute_ids);
		$this->groups[$group_name] = $group;
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

		foreach ($this->attributes as $attribute => $attribute_details)
		{
			foreach ($this->attributes[$attribute]['values'] as $value => $value_details)
			{
				$this->valueAdapter->UpdateFrequency($value_details['id'], $this->attributes[$attribute]['values'][$value]['frequency']);
			}
		}

		$db->transComplete();
	}

	protected function determineAttributesType()
	{
		$db = \Config\Database::connect();

		$ontologyPrefixes = $this->ontologyPrefixModel->getDistinctOntologyPrefixes();

		$db->transStart();
		foreach ($this->attributes as $attribute => $attribute_details)
		{
			$attribute_id = $attribute_details['id'];
			$attribute_type = $this->attributeAdapter->ReadType($attribute_id);

			if (
				!is_null($attribute_type) &&
				$attribute_type == ATTRIBUTE_TYPE_STRING &&
				$this->valueAdapter->CountByAttributeId($attribute_id) > 0
			)
			{
				$this->attributes[$attribute]['type'] = $attribute_type;
				continue; // If attribute has already values that are string, then skip and go to the next value.
			}

			$assumed_type = count($this->attributes[$attribute]['values']) > 0 ? ATTRIBUTE_TYPE_NUMERIC_NATURAL : $attribute_type; //Start with natural number and switch to other types if any instance is found

			$c = 0;
			$minMaxArray = $this->attributeAdapter->ReadMinimumAndMaximum($attribute_id);
			if (count($minMaxArray) == 2){
				$minimum_value = $minMaxArray[0];
				$maximum_value = $minMaxArray[1];
				$c++; // to skip the initialization in the below loop
			}

			foreach ($this->attributes[$attribute]['values'] as $value => $value_details)
			{
				if ($c == 0){
					//preset minimum and maximum
					$minimum_value = $value;
					$maximum_value = $value;
				}

				if ($value == intval($value)) $value = intval($value);
				else if ($value == floatval($value)) $value = floatval($value);

				if (is_numeric($value))
				{
					if (is_integer($value) && $value < 0)
					{
						$assumed_type = ATTRIBUTE_TYPE_NUMERIC_INTEGER;
					}
					elseif (is_float($value))
					{
						$assumed_type = ATTRIBUTE_TYPE_NUMERIC_REAL;
					}
					if ($value > $maximum_value) $maximum_value = $value;
					if ($value < $minimum_value) $minimum_value = $value;
				}
				elseif (is_string($value))
				{
					if ($this->valueStartsWithOntologyPrefix($value, $ontologyPrefixes))
					{
						$assumed_type = ATTRIBUTE_TYPE_ONTOLOGY_TERM;
					}
					else
					{
						$assumed_type = ATTRIBUTE_TYPE_STRING;
						$minimum_value = null;
						$maximum_value = null;
						break; // String is the most general type. If the code reaches this point, there is no need to iterate more.
					}
				}
				$c++;
			}
			$this->attributes[$attribute]['type'] = $assumed_type;
			$this->attributeAdapter->UpdateType($attribute_id,  $assumed_type);
			if (is_numeric($minimum_value) && is_numeric($maximum_value))
			{
				$this->attributeAdapter->UpdateMinimumAndMaximum($attribute_id, $minimum_value, $maximum_value);
			}
		}
		$db->transComplete();
	}

	protected function determineAttributesStorageLocation()
	{
		$db = \Config\Database::connect();
		$db->transStart();
		foreach ($this->attributes as $attribute => $attribute_details)
		{
			// If attribute exists in HPO, Negated HPO, ORPHA values of the pipeline, then it is stored in Neo4J
			// Otherwise it is stored on Elasticsearch
			$attribute_id = $attribute_details['id'];
			if ($attribute_details['type'] == ATTRIBUTE_TYPE_ONTOLOGY_TERM)
			{
				$this->attributeAdapter->UpdateStorageLocation($attribute_id, ATTRIBUTE_STORAGE_NEO4J);
			}
			else
			{
				$this->attributeAdapter->UpdateStorageLocation($attribute_id, ATTRIBUTE_STORAGE_ELASTICSEARCH);
			}
		}
		$db->transComplete();
	}

	private function valueStartsWithOntologyPrefix(string $value, array $prefixes): bool
	{
		for ($i = 0; $i < count($prefixes); $i++)
		{
			if (str_starts_with($value, $prefixes[$i]))
			{
				return true;
			}
		}

		return false;
	}

	protected function generateSubjectId(string $prefix = ''): string
	{
		helper('text');
		return $prefix . (strlen($prefix) > 0 ? '_' : '') . random_string('alnum', 36 - strlen($prefix));
	}

	protected function detectDelimiter(string $line): string
	{
		$delimiters = [',', ':', " ", "\t"];

		$max_count = 0;
		$final_delimiter = "";
		foreach ($delimiters as $delimiter)
		{
			if (str_contains($line, $delimiter) && count(explode($delimiter, $line)) > $max_count)
			{
				$max_count = count(explode($delimiter, $line));
				$final_delimiter = $delimiter;
			}
		}
		return $final_delimiter;
	}
}
