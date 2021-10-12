<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Index;

use App\Libraries\CafeVariome\Net\ServiceInterface;
use App\Models\Attribute;
use App\Models\EAV;
use App\Models\Source;

/**
 * Name AbstractDataIndex.php
 *
 * Created 01/10/2021
 * @author Mehdi Mehtarizadeh
 *
 */

abstract class AbstractDataIndex
{
	protected Attribute $attributeModel;
	protected bool $append;
	protected $dbInstance;
	protected string $jobName;
	protected int $sourceId;
	protected string $sourceName;
	protected string $sourceUID;
	protected EAV $eavModel;
	protected ServiceInterface $serviceInterface;
	protected Source $sourceModel;
	protected array $attributes;
	protected int $totalEAVsCount;
	protected int $totalRecords;
	protected int $processedRecords;


	public function __construct(int $source_id)
	{
		$this->sourceId = $source_id;
		$this->attributeModel = new Attribute();
		$this->eavModel = new EAV();
		$this->serviceInterface = new ServiceInterface();
		$this->sourceModel = new Source();
		$this->attributes = [];
	}

	abstract public function IndexSource();

	protected function getAttributeNameById(int $attribute_id): ?string
	{
		if (array_key_exists($attribute_id, $this->attributes)){
			return $this->attributes[$attribute_id]['name'];
		}
		else{
			$attribute = $this->attributeModel->getAttributeAndValues($attribute_id);
			if ($attribute != null){
				$this->attributes[$attribute_id] = $attribute;
				return $attribute['name'];
			}
		}
		return null;
	}

	protected function getAttributeById(int $attribute_id): ?array
	{
		if (array_key_exists($attribute_id, $this->attributes)){
			return $this->attributes[$attribute_id];
		}
		else{
			$attribute = $this->attributeModel->getAttributeAndValues($attribute_id);
			if ($attribute != null){
				$this->attributes[$attribute_id] = $attribute;
				return $attribute;
			}
		}
		return null;
	}

	protected function getValueNameByIdAndAttributeId(int $value_id, int $attribute_id): ?string
	{
		if (array_key_exists($attribute_id, $this->attributes)){
			$values = $this->attributes[$attribute_id]['values'];
			if(array_key_exists($value_id, $values)){
				return $values[$value_id];
			}
		}
		return null;
	}

	protected function initiateSource()
	{
		$source = $this->sourceModel->getSource($this->sourceId);
		if ($source){
			$this->sourceName = $source['name'];
			$this->sourceUID = $source['uid'];
		}
		else{
			throw new \Exception('Source does not exist');
		}
	}

	protected function registerProcess(int $source_id, string $message ='Starting')
	{
		$this->serviceInterface->RegisterProcess($source_id, 1, $this->jobName, $message);
	}

	protected function reportProgress(int $source_id, int $records_processed, int $total_records, string $status = "", bool $finished = false)
	{
		$this->serviceInterface->ReportProgress($source_id, $records_processed, $total_records, $this->jobName, $status, $finished);
	}

}
