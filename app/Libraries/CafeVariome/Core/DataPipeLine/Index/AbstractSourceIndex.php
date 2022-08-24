<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Index;

/**
 * Name AbstractSourceIndex.php
 *
 * Created 01/10/2021
 * @author Mehdi Mehtarizadeh
 *
 */

use App\Libraries\CafeVariome\Database\AttributeAdapter;
use App\Libraries\CafeVariome\Database\EAVAdapter;
use App\Libraries\CafeVariome\Database\SourceAdapter;
use App\Libraries\CafeVariome\Database\SubjectAdapter;
use App\Libraries\CafeVariome\Database\ValueAdapter;
use App\Libraries\CafeVariome\Entities\ViewModels\AttributeNameType;
use App\Libraries\CafeVariome\Entities\ViewModels\ValueName;
use App\Libraries\CafeVariome\Factory\AttributeAdapterFactory;
use App\Libraries\CafeVariome\Factory\EAVAdapterFactory;
use App\Libraries\CafeVariome\Factory\SourceAdapterFactory;
use App\Libraries\CafeVariome\Factory\SubjectAdapterFactory;
use App\Libraries\CafeVariome\Factory\ValueAdapterFactory;
use App\Libraries\CafeVariome\Net\ServiceInterface;

abstract class AbstractSourceIndex
{
	protected bool $continue;
	protected int $taskId;
	protected AttributeAdapter $attributeAdapter;
	protected ValueAdapter $valueAdapter;
	protected bool $overwrite;
	protected $dbInstance;
	protected string $jobName;
	protected int $sourceId;
	protected string $sourceName;
	protected string $sourceUID;
	protected EAVAdapter $EAVAdapter;

	protected ServiceInterface $serviceInterface;
	protected SourceAdapter $sourceAdapter;
	protected SubjectAdapter $subjectAdapter;
	protected array $attributes;
	protected array $subjects;
	protected int $totalEAVsCount;
	protected int $totalRecords;
	protected int $processedRecords;

	public function __construct(int $source_id)
	{
		$this->sourceId = $source_id;
		$this->attributeAdapter = (new AttributeAdapterFactory())->GetInstance();
		$this->valueAdapter = (new ValueAdapterFactory())->GetInstance();
		$this->EAVAdapter = (new EAVAdapterFactory())->GetInstance();
		$this->serviceInterface = new ServiceInterface();
		$this->sourceAdapter = (new SourceAdapterFactory())->GetInstance();
		$this->subjectAdapter = (new SubjectAdapterFactory())->GetInstance();
		$this->attributes = [];
		$this->subjects = [];
		$this->initiateSource();
	}

	abstract public function IndexSource();

	protected function getSubjectById(int $subject_id): ?string
	{
		if (array_key_exists($subject_id, $this->subjects))
		{
			return $this->subjects[$subject_id];
		}
		else
		{
			$subject = $this->subjectAdapter->Read($subject_id);
			if (!$subject->isNull())
			{
				$this->subjects[$subject->name] = $subject->name;
				return $subject->name;
			}
		}

		return null;
	}

	protected function getAttributeNameById(int $attribute_id): ?string
	{
		if (array_key_exists($attribute_id, $this->attributes))
		{
			return $this->attributes[$attribute_id]['name'];
		}
		else
		{
			$attribute = $this->attributeAdapter->SetModel(AttributeNameType::class)->Read($attribute_id);
			if (!$attribute->isNull())
			{
				$values = $this->valueAdapter->SetModel(ValueName::class)->ReadByAttributeId($attribute_id);
				$this->attributes[$attribute_id] = [
					'name' => $attribute->name,
					'type' => $attribute->type,
					'values' => $values
				];
				return $attribute->name;
			}
		}

		return null;
	}

	protected function getAttributeById(int $attribute_id): ?array
	{
		if (array_key_exists($attribute_id, $this->attributes))
		{
			return $this->attributes[$attribute_id];
		}
		else
		{
			$attribute = $this->attributeAdapter->SetModel(AttributeNameType::class)->Read($attribute_id);
			if (!$attribute->isNull())
			{
				$values = $this->valueAdapter->SetModel(ValueName::class)->ReadByAttributeId($attribute_id);
				$this->attributes[$attribute_id] = [
					'name' => $attribute->name,
					'type' => $attribute->type,
					'values' => $values
				];
				return $this->attributes[$attribute_id];
			}
		}

		return null;
	}

	protected function getValueNameByIdAndAttributeId(int $value_id, int $attribute_id): ?string
	{
		if (array_key_exists($attribute_id, $this->attributes))
		{
			$values = $this->attributes[$attribute_id]['values'];
			if(array_key_exists($value_id, $values))
			{
				return $values[$value_id]->name;
			}
		}

		return null;
	}

	protected function initiateSource()
	{
		$source = $this->sourceAdapter->Read($this->sourceId);
		if (!$source->isNull())
		{
			$this->sourceName = $source->name;
			$this->sourceUID = $source->uid;
		}
		else
		{
			throw new \Exception('Source does not exist');
		}
	}

	protected function CalculateProgressInPercent(): int
	{
		return intval(ceil((($this->processedRecords / $this->totalRecords)) * 100.0));
	}

	protected function ReportProgress(?int $progress = null, string $status = '', bool $finished = false)
	{
		if (is_null($progress))
		{
			$progress = $this->CalculateProgressInPercent();
		}
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

}
