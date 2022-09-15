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
use App\Libraries\CafeVariome\Database\EAVAdapter;
use App\Libraries\CafeVariome\Database\GroupAdapter;
use App\Libraries\CafeVariome\Database\PipelineAdapter;
use App\Libraries\CafeVariome\Database\SourceAdapter;
use App\Libraries\CafeVariome\Database\SubjectAdapter;
use App\Libraries\CafeVariome\Database\ValueAdapter;
use App\Libraries\CafeVariome\Entities\ViewModels\AttributeID;
use App\Libraries\CafeVariome\Entities\ViewModels\SubjectID;
use App\Libraries\CafeVariome\Entities\ViewModels\ValueAttributeID;
use App\Libraries\CafeVariome\Factory\AttributeAdapterFactory;
use App\Libraries\CafeVariome\Factory\DataFileAdapterFactory;
use App\Libraries\CafeVariome\Factory\EAVAdapterFactory;
use App\Libraries\CafeVariome\Factory\GroupAdapterFactory;
use App\Libraries\CafeVariome\Factory\PipelineAdapterFactory;
use App\Libraries\CafeVariome\Factory\SourceAdapterFactory;
use App\Libraries\CafeVariome\Factory\SubjectAdapterFactory;
use App\Libraries\CafeVariome\Factory\TaskAdapterFactory;
use App\Libraries\CafeVariome\Factory\TaskFactory;
use App\Libraries\CafeVariome\Factory\ValueAdapterFactory;
use App\Libraries\CafeVariome\Helpers\Shell\PHPShellHelper;

class DataPipeLine
{
	protected int $sourceId;
	protected int $pipelineId;
	protected int $overwrite;
	protected int $taskId;
	protected int $userId;
	protected bool $continue;

	protected string $basePath;
	protected array $configuration;
	protected EAVAdapter $EAVAdapter;
	protected DataFileAdapter $dataFileAdapter;
	protected SourceAdapter $sourceAdapter;
	protected SubjectAdapter $subjectAdapter;
	protected GroupAdapter $groupAdapter;
	protected AttributeAdapter $attributeAdapter;
	protected ValueAdapter $valueAdapter;
	protected PipelineAdapter $piplineAdapter;

	public function __construct(int $source_id)
	{
		$this->sourceId = $source_id;

		$this->EAVAdapter = (new EAVAdapterFactory())->GetInstance();
		$this->dataFileAdapter = (new DataFileAdapterFactory())->GetInstance();
		$this->sourceAdapter = (new SourceAdapterFactory())->GetInstance();
		$this->subjectAdapter = (new SubjectAdapterFactory())->GetInstance();
		$this->groupAdapter = (new GroupAdapterFactory())->GetInstance();
		$this->attributeAdapter = (new AttributeAdapterFactory())->GetInstance();
		$this->valueAdapter = (new ValueAdapterFactory())->GetInstance();
		$this->piplineAdapter = (new PipelineAdapterFactory())->GetInstance();

		$this->basePath = FCPATH . UPLOAD . UPLOAD_DATA . $this->sourceId . DIRECTORY_SEPARATOR;

		$this->fileMan = new FileMan($this->basePath);
	}


	protected function UpdateSourceSubjectCount()
	{
		$totalRecordCount = $this->subjectAdapter->CountBySourceId($this->sourceId);
		$this->sourceAdapter->UpdateRecordCount($this->sourceId, $totalRecordCount);
	}

	public function DeleteExistingRecords(int $file_id)
	{
		$db = \Config\Database::connect();
		$db->transStart();
		$valueFrequencies = $this->EAVAdapter->ReadValueFrequenciesBySourceIdAndFileId($this->sourceId, $file_id); // Get value frequencies
		if (count($valueFrequencies) > 0)
		{
			$this->EAVAdapter->DeleteByFileId($file_id); // Delete records from eavs
			$valueCount = count($valueFrequencies);
			for ($i = 0; $i < $valueCount; $i++)
			{
				$value_id = $valueFrequencies[$i]->value_id;
				$value_frequency = $valueFrequencies[$i]->frequency;
				$this->valueAdapter->UpdateFrequency($value_id, -$value_frequency);
				$this->valueAdapter->DeleteIfAbsent($value_id); // Delete value if frequency is 0
				unset($valueFrequencies[$i]);
			}
			unset($value_id);
		}
		$db->transComplete();

		$this->SynchroniseAttributes();
		$this->dataFileAdapter->UpdateRecordCount($file_id, 0);
		$this->SynchroniseSubjectIDs();
		$this->UpdateSourceSubjectCount();
	}

	protected function SynchroniseAttributes()
	{
		$db = \Config\Database::connect();
		$db->transStart();
		$valueAttributeIDs = $this->valueAdapter->SetModel(ValueAttributeID::class)->ReadAll();
		$stateAttributeIDs = [];
		foreach ($valueAttributeIDs as &$valueAttributeID)
		{
			array_push($stateAttributeIDs, $valueAttributeID->attribute_id);
		}
		$attributeIDs = array_keys($this->attributeAdapter->SetModel(AttributeID::class)->ReadBySourceIdWithNoValues($this->sourceId, $stateAttributeIDs));
		$this->attributeAdapter->DeleteByIds($attributeIDs);
		$db->transComplete();
	}

	protected function SynchroniseSubjectIDs()
	{
		$db = \Config\Database::connect();
		$db->transStart();
		$subjectIDs = array_keys($this->subjectAdapter->SetModel(SubjectID::class)->ReadAllBySourceId($this->sourceId));
		$EAVSubjectIDs = $this->EAVAdapter->ReadUniqueSubjectIdsBySourceId($this->sourceId);
		$subjectIDCount = count($subjectIDs);

		for($i = 0; $i < $subjectIDCount; $i++)
		{
			if (in_array($subjectIDs[$i], $EAVSubjectIDs))
			{
				unset($subjectIDs[$i]);
			}
		}

		$this->subjectAdapter->DeleteByIds($subjectIDs); // Subject IDs that need to be removed from the DB
		$db->transComplete();
	}

	public function CreateUIIndex(?int $user_id = null)
	{
		$this->sourceAdapter->Lock($this->sourceId);

		$task = (new TaskFactory())->GetInstanceFromParameters(
			$user_id ?? $this->userId,
			TASK_TYPE_SOURCE_INDEX_USER_INTERFACE,
			0,
			TASK_STATUS_CREATED,
			-1,
			null,
			null,
			null,
			null,
			null,
			$this->sourceId,
			false
		);

		$taskAdapter = (new TaskAdapterFactory())->GetInstance();
		$taskId = $taskAdapter->Create($task);

		// Start the task through CLI
		PHPShellHelper::runAsync(getcwd() . "/index.php Task Start $taskId");
	}
}
