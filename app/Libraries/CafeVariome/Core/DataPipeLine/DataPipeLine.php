<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine;

/**
 * Name DataPipeLine.php
 *
 * Created 29/07/2022
 * @author Mehdi Mehtarizadeh
 *
 */

use App\Libraries\CafeVariome\Core\IO\FileSystem\FileMan;
use App\Libraries\CafeVariome\Database\AttributeAdapter;
use App\Libraries\CafeVariome\Database\DataFileAdapter;
use App\Libraries\CafeVariome\Database\GroupAdapter;
use App\Libraries\CafeVariome\Database\SourceAdapter;
use App\Libraries\CafeVariome\Database\SubjectAdapter;
use App\Libraries\CafeVariome\Database\ValueAdapter;
use App\Libraries\CafeVariome\Factory\AttributeAdapterFactory;
use App\Libraries\CafeVariome\Factory\DataFileAdapterFactory;
use App\Libraries\CafeVariome\Factory\GroupAdapterFactory;
use App\Libraries\CafeVariome\Factory\SourceAdapterFactory;
use App\Libraries\CafeVariome\Factory\SubjectAdapterFactory;
use App\Libraries\CafeVariome\Factory\ValueAdapterFactory;
use App\Models\EAV;
use App\Models\Pipeline;

class DataPipeLine
{
	protected int $sourceId;
	protected int $pipelineId;
	protected int $overwrite;
	protected int $taskId;
	protected bool $continue;

	protected string $basePath;
	protected array $configuration;
	protected DataFileAdapter $dataFileAdapter;
	protected SourceAdapter $sourceAdapter;
	protected SubjectAdapter $subjectAdapter;
	protected GroupAdapter $groupAdapter;
	protected AttributeAdapter $attributeAdapter;
	protected ValueAdapter $valueAdapter;

	public function __construct(int $source_id)
	{
		$this->sourceId = $source_id;

		$this->dataFileAdapter = (new DataFileAdapterFactory())->GetInstance();
		$this->sourceAdapter = (new SourceAdapterFactory())->GetInstance();
		$this->subjectAdapter = (new SubjectAdapterFactory())->GetInstance();
		$this->groupAdapter = (new GroupAdapterFactory())->GetInstance();
		$this->attributeAdapter = (new AttributeAdapterFactory())->GetInstance();
		$this->valueAdapter = (new ValueAdapterFactory())->GetInstance();

		$this->basePath = FCPATH . UPLOAD . UPLOAD_DATA . $this->sourceId . DIRECTORY_SEPARATOR;

		$this->eavModel = new EAV();
		$this->pipelineModel = new Pipeline();
		$this->fileMan = new FileMan($this->basePath);
	}

	public function DeleteExistingRecords(int $file_id)
	{
		$db = \Config\Database::connect();
		$db->transStart();
		$valueFrequencies = $this->eavModel->getValueFrequenciesBySourceIdAndFileId($this->sourceId, $file_id); // Get value frequencies
		if (count($valueFrequencies) > 0)
		{
			$this->eavModel->deleteRecordsByFileId($file_id); // Delete records from eavs
			$valueCount = count($valueFrequencies);
			for ($i = 0; $i < $valueCount; $i++)
			{
				$value_id = $valueFrequencies[$i]['value_id'];
				$value_frequency = $valueFrequencies[$i]['frequency'];
				$this->valueAdapter->UpdateFrequency($value_id, -$value_frequency);
				$this->valueAdapter->DeleteIfAbsent($value_id); // Delete value if frequency is 0
				unset($valueFrequencies[$i]);
			}
			unset($value_id);
		}
		$db->transComplete();
	}
}
