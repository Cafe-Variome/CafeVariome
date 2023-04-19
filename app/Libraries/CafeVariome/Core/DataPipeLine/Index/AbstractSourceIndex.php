<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Index;

/**
 * Name AbstractSourceIndex.php
 *
 * Created 01/10/2021
 * @author Mehdi Mehtarizadeh
 *
 *  This abstract class defines the structure for indexing an individual source.
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
use DateTime;

abstract class AbstractSourceIndex
{
	/**
	 * @var bool whether the process should continue or stop.
	 */
	protected bool $continue;

	/**
	 * @var int Task ID associated with process
	 */
	protected int $taskId;

	/**
	 * @var bool flag that determines whether the index needs to be overwritten or not.
	 */
	protected bool $overwrite;
	protected $dbInstance;
	/**
	 * @var int Id of the source to be indexed
	 */
	protected int $sourceId;

	/**
	 * @var string name od source as saved in database
	 */
	protected string $sourceName;

	/**
	 * @var string UID of source
	 * @see Source definition
	 */
	protected string $sourceUID;

	/**
	 * @var IAdapter
	 */
	protected IAdapter $attributeAdapter;

	/**
	 * @var IAdapter
	 */
	protected IAdapter $valueAdapter;

	/**
	 * @var IAdapter
	 */
	protected IAdapter $EAVAdapter;

	/**
	 * @var IAdapter
	 */
	protected IAdapter $sourceAdapter;

	/**
	 * @var IAdapter
	 */
	protected IAdapter $subjectAdapter;

	/**
	 * @var ServiceInterface Service Interface object to interact with the demon
	 */
	protected ServiceInterface $serviceInterface;

	/**
	 * @var array of source attributes
	 */
	protected array $attributes;

	/**
	 * @var array of subject Ids
	 */
	protected array $subjects;

	/**
	 * @var int count of total EAV records
	 */
	protected int $totalEAVsCount;

	/**
	 * @var int count of all records
	 */
	protected int $totalRecords;

	/**
	 * @var int number of indexed records
	 */
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
		return $this->totalRecords == 0 ? 0 : intval(ceil((($this->processedRecords / $this->totalRecords)) * 100.0));
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

	protected function ParseDate(string $input, array $formats, string $reformat): string | false
	{
		for ($i = 0; $i < count($formats); $i++)
		{
			$d = DateTime::createFromFormat($formats[$i], $input);
			if ($d && $d->format($formats[$i]) === $input)
			{
				return $d->format($reformat);
			}
		}
		return false;
	}

	protected function getDateFormats(): array
	{
		return [
			'Y-m-d', 'd-m-Y', 'Y-m', 'm-Y', 'Y/m/d', 'd/m/Y', 'm/Y', 'Y/m', 'm-d-Y', 'Y-d-m', 'Y-m-d H:i:s', 'Y/m/d H:i:s'
		];
	}

}
