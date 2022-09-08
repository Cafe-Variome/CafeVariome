<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Index;

/**
 * Name UserInterfaceDataIndex.php
 *
 * Created 14/10/2021
 * @author Mehdi Mehtarizadeh
 *
 */

use App\Libraries\CafeVariome\Core\IO\FileSystem\SysFileMan;
use App\Libraries\CafeVariome\Entities\Task;

class UserInterfaceSourceIndex extends AbstractSourceIndex
{
	public function __construct(Task $task)
	{
		parent::__construct($task->source_id);
		$this->continue = true;
		$this->overwrite = $task->overwrite;
		$this->taskId = $task->getID();

		$this->processedRecords = 0;
		$this->totalRecords = 0;
		$this->totalEAVsCount = 0;
	}

    public function IndexSource()
    {
		$attributes = $this->attributeAdapter->ReadBySourceId($this->sourceId, true);
		$this->totalRecords = count($attributes);

		$this->ReportProgress(null, 'Generating user interface index');

		$attributesValues = [];
		$attributesDisplayNames = [];
		$valuesDisplayNames = [];

		foreach($attributes as &$attribute)
		{
			$attributeValues = [];

			$attribute_name = $attribute->name;
			$attributesDisplayNames[$attribute_name] = $attribute->display_name;

			$values = $this->valueAdapter->ReadByAttributeId($attribute->getID(), true);

			foreach($values as $value)
			{
				$value_name = $value->name;
				$value_display_name = $value->display_name;

				array_push($attributeValues, $value->name);
				if (array_key_exists($value_name, $valuesDisplayNames))
				{
					if (!in_array($value_display_name, $valuesDisplayNames[$value_name]))
					{
						array_push($valuesDisplayNames[$value_name], $value_display_name);
					}
				}
				else
				{
					$valuesDisplayNames[$value_name] = [$value_display_name];
				}
			}

			$attributesValues[$attribute_name] = $attributeValues;

			$this->processedRecords++;
			$this->ReportProgress();
		}

		$indexData = [
			'source_id' => $this->sourceId,
			'attributes_values' => $attributesValues,
			'attributes_display_names' => $attributesDisplayNames,
			'values_display_names' => $valuesDisplayNames,
		];

		$uiIndexPath = getcwd() . DIRECTORY_SEPARATOR . USER_INTERFACE_INDEX_DIR;
		$indexName = $this->sourceId . '_' . $this->sourceUID . '.json';
		$fileMan = new SysFileMan($uiIndexPath);

		if ($fileMan->Exists($indexName)){
			$fileMan->Delete($indexName);
		}

		$fileMan->Write($indexName, json_encode($indexData));

		$this->sourceAdapter->Unlock($this->sourceId);

		$this->ReportProgress(null, 'Finished', true);
	}
}
