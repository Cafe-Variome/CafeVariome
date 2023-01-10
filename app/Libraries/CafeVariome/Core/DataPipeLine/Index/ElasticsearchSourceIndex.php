<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Index;

/**
 * Name ElasticsearchDataIndex.php
 *
 * Created 01/10/2021
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 *
 */

use App\Libraries\CafeVariome\CafeVariome;
use App\Libraries\CafeVariome\Entities\Task;
use App\Libraries\CafeVariome\Helpers\Core\ElasticsearchHelper;

class ElasticsearchSourceIndex extends AbstractSourceIndex
{
	public function __construct(Task $task)
	{
		parent::__construct($task->source_id);
		$this->continue = true;
		$this->overwrite = $task->overwrite;
		$this->taskId = $task->getID();

		$this->dbInstance =  new ElasticSearch([$this->getEndPointURI()]);
		$this->processedRecords = 0;
		$this->totalRecords = 0;
		$this->totalEAVsCount = 0;
	}

    public function IndexSource()
    {
		$index_name = ElasticsearchHelper::GetSourceIndexName($this->sourceId);

		if ($this->overwrite)
		{
			$this->dbInstance->deleteIndex($index_name);
		}

		if (!$this->dbInstance->indexExists($index_name))
		{
			$this->dbInstance->createIndex($index_name, $this->getMapping());
		}

		//Get attribute Ids that must be stored on Elasticsearch
		$attribute_ids = $this->attributeAdapter->ReadIdsBySourceIdAndStorageLocation($this->sourceId, ATTRIBUTE_STORAGE_ELASTICSEARCH);

		$this->sourceAdapter->Lock($this->sourceId);

		$this->createParentDocuments($attribute_ids);
		$this->createChildDocuments($attribute_ids);

		$this->EAVAdapter->UpdateIndexedBySourceIdAndAttributeIds($this->sourceId, $attribute_ids);

		$this->sourceAdapter->Unlock($this->sourceId);

		$this->ReportProgress(100, 'Finished', true);
	}

	private function createParentDocuments(array $attribute_ids)
	{
		$index_name = ElasticsearchHelper::GetSourceIndexName($this->sourceId);
		$bulk = [];
		$offset = 0;
		$currId = 0;
		$batchSize = EAV_BATCH_SIZE;

		// Get total EAV records by source_id
		$this->totalEAVsCount = $this->EAVAdapter->CountBySourceIdAndAttributeIds($this->sourceId, $attribute_ids, !$this->overwrite);
		// Get unique subject_ids by source_id
		$uniqueUIDsCount = $this->EAVAdapter->CountUniqueGroupsBySourceIdAndAttributeIds($this->sourceId, $attribute_ids, !$this->overwrite);
		//Total number of Elasticsearch documents to be created.
		$this->totalRecords = $this->totalEAVsCount + $uniqueUIDsCount;

		$this->ReportProgress(0, 'Generating Elasticsearch index');

		while ($offset < $uniqueUIDsCount)
		{
			if (is_null($currId)) break;

			$unique_ids = $this->EAVAdapter->ReadUniqueGroupIdsAndSubjectIdsBySourceIdAndAttributeIds($this->sourceId, $attribute_ids, $batchSize, $currId, !$this->overwrite);
			$uid_count = count($unique_ids);

			if ($uid_count == 0)
			{
				break;
			}

			$currId = $this->EAVAdapter->ReadLastIdByGroupIdAndSubjectId($unique_ids[$uid_count - 1]->subject_id, $unique_ids[$uid_count - 1]->group_id);

			// start making all the parent documents in ElasticSearch
			for ($i = 0; $i < $uid_count; $i++)
			{
				$bulk['body'][] = [
					'index'=>[
						'_id' => $unique_ids[$i]->subject_id . '_' . $unique_ids[$i]->group_id,
						'_index' => $index_name
					]
				];
				$bulk['body'][] = [
					'subject_id' => $this->getSubjectById($unique_ids[$i]->subject_id),
					'eav_rel' => [
						'name' => 'sub'
					],
					'type' => 'sub',
					'source_id' => $this->sourceId,
					'file_id' => $unique_ids[$i]->data_file_id
				];

				unset($unique_ids[$i]);
				$this->processedRecords++;

				if ($this->processedRecords % EAV_BATCH_SIZE == 0)
				{
					$responses = $this->dbInstance->indexDocuments($bulk);
					$bulk = [];
					unset ($responses);

					$this->ReportProgress();
				}
			}

			$offset += $batchSize;
		}

		// Send the last documents through, if any
		if (!empty($bulk['body']))
		{
			$responses = $this->dbInstance->indexDocuments($bulk);
			$bulk = [];
			unset ($responses);

			$this->ReportProgress();
		}
	}

	private function createChildDocuments(array $attribute_ids)
	{
		$index_name = ElasticsearchHelper::GetSourceIndexName($this->sourceId);
		$bulk=[];
		$offset = 0;
		$currId = 0;
		$batchSize = EAV_BATCH_SIZE;
		$dateFormats = $this->getDateFormats();

		while ($offset < $this->totalEAVsCount)
		{
			// Get current limit chunk of data
			$eavdata = $this->EAVAdapter->ReadBySourceIdAndAttributeIds($this->sourceId, $attribute_ids, $batchSize, $currId, !$this->overwrite);
			$eav_count = count($eavdata);
			$lastRec = end($eavdata);
			$currId = $lastRec->id;
			reset($eavdata);

			// Loop through limit chunk
			for ($i = 0; $i < $eav_count; $i++)
			{
				$attribute_id = $eavdata[$i]->attribute_id;
				$value_id = $eavdata[$i]->value_id;
				$subject_id = $eavdata[$i]->subject_id;
				$group_id = $eavdata[$i]->group_id;
				$subject_name = $this->getSubjectById($subject_id);
				$attribute = $this->getAttributeById($attribute_id);
				$attribute_name = $attribute['name'];
				$attribute_type = $attribute['type'];
				$value_name = $this->getValueNameByIdAndAttributeId($value_id, $attribute_id);

				if($attribute_type == ATTRIBUTE_TYPE_DATETIME)
				{
					$value_name = $this->ParseDate($value_name, $dateFormats, 'Y-m-d');
				}

				$bulk['routing'] = $subject_id . '_' . $group_id;
				$bulk['body'][] = [
					'index' => [
						'_index' => $index_name
					]
				];
				$bulk['body'][] = [
					'subject_id' => $subject_name,
					'attribute' => $attribute_name,
					'value' => strtolower($value_name),
					'eav_rel' => [
						'name' => 'eav',
						'parent' => $subject_id . '_' . $group_id
					],
					'type' => 'eav',
					'source_id' => $this->sourceId,
					'file_id' => $eavdata[$i]->data_file_id
				];

				unset($eavdata[$i]);
				$this->processedRecords++;

				if ($this->processedRecords % EAV_BATCH_SIZE == 0)
				{
					$responses = $this->dbInstance->indexDocuments($bulk);
					$bulk=[];
					unset ($responses);

					$this->ReportProgress();
				}
			}

			// Update offset
			$offset += $batchSize;
		}

		// Send the last set of documents through
		if (!empty($bulk['body']))
		{
			$responses = $this->dbInstance->indexDocuments($bulk);

			$this->ReportProgress();
			unset ($responses);
		}
	}

	private function getMapping(): array
	{
		$map = '{
                "mappings":{
                    "properties":{
                        "eav_rel":{"type": "join", "relations": {"sub":"eav"}},
                        "type": { "type": "keyword" },
                        "subject_id": {"type": "keyword"},
                        "file_id": {"type":"keyword"},
                        "source_id": {"type":"keyword"},
                        "attribute":{"type":"keyword"},
                        "value":{
                            "type":"text",
                            "fields":
                                {
									"raw":{"type": "keyword"},
									"d":{"type": "double", "ignore_malformed": "true"},
									"dt":{"type": "date", "ignore_malformed": "true", "format": "date"}
								}
                        }
                    }
                }
            }';
		return json_decode($map,true);
	}

	private function getEndPointURI()
	{
		$setting = CafeVariome::Settings();
		return $setting->GetElasticSearchUri();
	}
}
