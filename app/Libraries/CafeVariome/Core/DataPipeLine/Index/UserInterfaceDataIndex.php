<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Index;

use App\Libraries\CafeVariome\Core\IO\FileSystem\SysFileMan;
use App\Models\Value;

/**
 * Name UserInterfaceDataIndex.php
 *
 * Created 14/10/2021
 * @author Mehdi Mehtarizadeh
 *
 */

class UserInterfaceDataIndex extends AbstractDataIndex
{
	private Value $valueModel;

	public function __construct(int $source_id)
	{
		parent::__construct($source_id);
		$this->initiateSource();
		$this->jobName = 'uiindex';
		$this->processedRecords = 0;
		$this->totalRecords = 0;
		$this->totalEAVsCount = 0;
		$this->valueModel = new Value();
	}

    public function IndexSource()
    {
		$this->registerProcess($this->sourceId);

		$attributes = $this->attributeModel->getAttributesBySourceId($this->sourceId, true);
		$this->totalRecords = count($attributes);

		$this->reportProgress($this->sourceId, $this->processedRecords, $this->totalRecords, 'Generating user interface index');

		$attributesValues = [];
		$attributesDisplayNames = [];
		$valuesDisplayNames = [];

		$attributesCount = count($attributes);
		for($i = 0; $i < $attributesCount; $i++)
		{
			$attributeValues = [];

			$attribute = $attributes[$i];
			$attribute_name = $attribute['name'];
			$attributesDisplayNames[$attribute_name] = $attribute['display_name'];

			$values = $this->valueModel->getValuesByAttributeId($attribute['id'], true);
			$valuesCount = count($values);
			for($j = 0; $j < $valuesCount; $j++)
			{
				$value_name = $values[$j]['name'];
				$value_display_name = $values[$j]['display_name'];

				array_push($attributeValues, $values[$j]['name']);
				if (array_key_exists($value_name, $valuesDisplayNames)){
					if (!in_array($value_display_name, $valuesDisplayNames[$value_name])){
						array_push($valuesDisplayNames[$value_name], $value_display_name);
					}
				}
				else{
					$valuesDisplayNames[$value_name] = [$value_display_name];
				}
				unset($values[$j]);
			}

			$attributesValues[$attribute_name] = $attributeValues;
			unset($attributes[$i]);

			$this->processedRecords++;
			$this->reportProgress($this->sourceId, $this->processedRecords, $this->totalRecords);
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

		$this->reportProgress($this->sourceId, 1, 1, 'Finished', true);
	}
}
