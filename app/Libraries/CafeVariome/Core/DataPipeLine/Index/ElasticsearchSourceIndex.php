<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Index;

/**
 * Name ElasticsearchDataIndex.php
 *
 * Created 01/10/2021
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 *
 */

use App\Libraries\CafeVariome\Helpers\Core\ElasticsearchHelper;
use \App\Models\Settings;

class ElasticsearchSourceIndex extends AbstractSourceIndex
{
	public function __construct(int $source_id, bool $append)
	{
		parent::__construct($source_id);
		$this->append = $append;
		$this->dbInstance =  new ElasticSearch([$this->getEndPointURI()]);
		$this->jobName = 'elasticsearchindex';
		$this->processedRecords = 0;
		$this->totalRecords = 0;
		$this->totalEAVsCount = 0;
	}

    public function IndexSource()
    {
		$this->registerProcess($this->sourceId);

		$index_name = ElasticsearchHelper::getSourceIndexName($this->sourceId);

		if (!$this->append){
			$this->dbInstance->deleteIndex($index_name);
		}

		if (!$this->dbInstance->indexExists($index_name)){
			$this->dbInstance->createIndex($index_name, $this->getMapping());
		}

		//Get attribute Ids that must be stored on Elasticsearch
		$attribute_ids = $this->attributeModel->getAttributeIdsBySourceIdAndStorageLocation($this->sourceId, ATTRIBUTE_STORAGE_ELASTICSEARCH);

		$this->createParentDocuments($attribute_ids);
		$this->createChildDocuments($attribute_ids);

		$this->eavModel->setIndexedFlagBySourceIdAndAttributeIds($this->sourceId, $attribute_ids);
		$this->sourceModel->unlockSource($this->sourceId);

		$this->reportProgress($this->sourceId, 1, 1, 'Finished', true);
	}

	private function createParentDocuments(array $attribute_ids)
	{
		$index_name = ElasticsearchHelper::getSourceIndexName($this->sourceId);
		$bulk = [];
		$offset = 0;
		$currId = 0;
		$batchSize = EAV_BATCH_SIZE;

		// Get total EAV records by source_id
		$this->totalEAVsCount = $this->eavModel->countEAVsBySourceIdAndAttributeIds($this->sourceId, $attribute_ids, $this->append);
		// Get unique subject_ids by source_id
		$uniqueUIDsCount = $this->eavModel->countUniqueUIDsBySourceIdAndAttributeIds($this->sourceId, $attribute_ids, $this->append);
		//Total number of Elasticsearch documents to be created.
		$this->totalRecords = $this->totalEAVsCount + $uniqueUIDsCount;

		$this->reportProgress($this->sourceId, $this->processedRecords, $this->totalRecords, 'Generating Elasticsearch index');

		while ($offset < $uniqueUIDsCount)
		{
			$unique_ids = $this->eavModel->getUniqueUIDsAndSubjectIdsBySourceIdAndAttributeIds($this->sourceId, $attribute_ids, $batchSize, $currId, $this->append);
			$uid_count = count($unique_ids);

			if ($uid_count == 0){
				break;
			}

			$currId = $this->eavModel->getLastIdByUID($unique_ids[$uid_count - 1]['uid']);

			// start making all the parent documents in ElasticSearch
			for ($i = 0; $i < $uid_count; $i++) {
				$bulk['body'][] = [
					'index'=>[
						'_id' => $unique_ids[$i]['uid'],
						'_index' => $index_name
					]
				];
				$bulk['body'][] = [
					'subject_id' => $unique_ids[$i]['subject_id'],
					'eav_rel' => [
						'name' => 'sub'
					],
					'type' => 'sub',
					'source_id' => $this->sourceId,
					'file_id' => $unique_ids[$i]['file_id']
				];

				unset($unique_ids[$i]);
				$this->processedRecords++;

				if ($this->processedRecords % EAV_BATCH_SIZE == 0){
					$responses = $this->dbInstance->indexDocuments($bulk);
					$bulk = [];
					unset ($responses);
					$this->reportProgress($this->sourceId, $this->processedRecords, $this->totalRecords);
				}
			}

			$offset += $batchSize;
		}

		// Send the last documents through, if any
		if (!empty($bulk['body'])){
			$responses = $this->dbInstance->indexDocuments($bulk);
			$bulk = [];
			unset ($responses);
			$this->reportProgress($this->sourceId, $this->processedRecords, $this->totalRecords);
		}
	}

	private function createChildDocuments(array $attribute_ids)
	{
		$index_name = ElasticsearchHelper::getSourceIndexName($this->sourceId);
		$bulk=[];
		$offset = 0;
		$currId = 0;
		$batchSize = EAV_BATCH_SIZE;

		while ($offset < $this->totalEAVsCount){
			// Get current limit chunk of data
			$eavdata = $this->eavModel->getEAVsBySourceIdAndAttributeIds($this->sourceId, $attribute_ids, $batchSize, $currId, $this->append);
			$eav_count = count($eavdata);
			$lastRec = end($eavdata);
			$currId = $lastRec['id'];
			reset($eavdata);

			// Loop through limit chunk
			for ($i = 0; $i < $eav_count; $i++) {
				//$eavdata[$i]['attribute'] = preg_replace('/\s+/', '_', $eavdata[$i]['attribute']);
				$attribute_id = $eavdata[$i]['attribute_id'];
				$value_id = $eavdata[$i]['value_id'];
				$attribute_name = $this->getAttributeNameById($attribute_id);
				$value_name = $this->getValueNameByIdAndAttributeId($value_id, $attribute_id);

				$bulk['routing'] = $eavdata[$i]['uid'];
				$bulk['body'][] = [
					'index' => [
						'_index' => $index_name
					]
				];
				$bulk['body'][] = [
					'subject_id' => $eavdata[$i]['subject_id'],
					'attribute' => $attribute_name,
					'value' => strtolower($value_name),
					'eav_rel' => [
						'name' => 'eav',
						'parent' => $eavdata[$i]['uid']
					],
					'type' => 'eav',
					'source_id' => $this->sourceId,
					'file_id' => $eavdata[$i]['file_id']
				];

				unset($eavdata[$i]);
				$this->processedRecords++;

				if ($this->processedRecords % EAV_BATCH_SIZE == 0){
					$responses = $this->dbInstance->indexDocuments($bulk);
					$bulk=[];
					unset ($responses);

					$this->reportProgress($this->sourceId, $this->processedRecords, $this->totalRecords);
				}
			}

			// Update offset
			$offset += $batchSize;
		}

		// Send the last set of documents through
		if (!empty($bulk['body'])){
			$responses = $this->dbInstance->indexDocuments($bulk);
			$this->reportProgress($this->sourceId, $this->processedRecords, $this->totalRecords);
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
									"dt":{"type": "date", "ignore_malformed": "true", "format": "dateOptionalTime"}
								}
                        }
                    }
                }
            }';
		return json_decode($map,true);
	}

	private function getEndPointURI()
	{
		$setting = Settings::getInstance();
		return $setting->getElasticSearchUri();
	}
}
