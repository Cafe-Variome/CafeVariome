<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Index;

use App\Models\Ontology;
use App\Models\OntologyPrefix;
use App\Models\OntologyRelationship;

/**
 * Name Neo4JDataIndex.php
 *
 * Created 06/10/2021
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 *
 */

class Neo4JDataIndex extends AbstractDataIndex
{
	private Ontology $ontologyModel;
	private OntologyPrefix $prefixModel;
	private OntologyRelationship $relationshipModel;
	private array $ontologies;
	private array $ontologyPrefixes;
	private array $ontologyRelationships;
	private array $ontologyAssociations;

	public function __construct(int $source_id, bool $append)
	{
		parent::__construct($source_id);
		$this->initiateSource($source_id);
		$this->append = $append;
		$this->dbInstance = new Neo4J();
		$this->jobName = 'neo4jindex';
		$this->processedRecords = 0;
		$this->totalRecords = 0;
		$this->totalEAVsCount = 0;
		$this->ontologyModel = new Ontology();
		$this->prefixModel = new OntologyPrefix();
		$this->relationshipModel = new OntologyRelationship();
		$this->ontologies = [];
		$this->ontologyPrefixes = [];
		$this->ontologyRelationships = [];
		$this->ontologyAssociations = [];
	}

    public function IndexSource()
    {
		$this->registerProcess($this->sourceId);

		if (!$this->append) {
			$this->dbInstance->deleteSource($this->sourceId);
		}

		//Get attribute Ids that must be stored on Elasticsearch
		$attribute_ids = $this->attributeModel->getAttributeIdsBySourceIdAndStorageLocation($this->sourceId, ATTRIBUTE_STORAGE_NEO4J);

		$this->createSubjectNodes($attribute_ids);
		$this->createRelationships($attribute_ids);

		$this->eavModel->setIndexedFlagBySourceIdAndAttributeIds($this->sourceId, $attribute_ids);
		$this->sourceModel->unlockSource($this->sourceId);

		$this->reportProgress($this->sourceId, 1, 1, 'Finished', true);
    }

	private function createSubjectNodes(array $attribute_ids)
	{
		$offset = 0;
		$currId = 0;
		$batchSize = NEO4J_BATCH_SIZE;

		// Get total EAV records by source_id (Neo4J relationships)
		$this->totalEAVsCount = $this->eavModel->countEAVsBySourceIdAndAttributeIds($this->sourceId, $attribute_ids, $this->append);
		// Get unique subject_ids by source_id
		$uniqueSubjectIdsCount = $this->eavModel->countUniqueSubjectIdsBySourceIdAndAttributeIds($this->sourceId, $attribute_ids, $this->append);
		//Total number of records to be created.
		$this->totalRecords = $this->totalEAVsCount + $uniqueSubjectIdsCount;

		$this->reportProgress($this->sourceId, $this->processedRecords, $this->totalRecords, 'Inserting subjects into Neo4J');

		while ($offset < $uniqueSubjectIdsCount)
		{
			$unique_subject_ids = $this->eavModel->getUniqueSubjectIdsBySourceIdAndAttributeIds($this->sourceId, $attribute_ids, $batchSize, $currId, $this->append);
			$subject_id_count = count($unique_subject_ids);

			if ($subject_id_count == 0){
				break;
			}

			$currId = $this->eavModel->getLastIdBySubjectId($unique_subject_ids[$subject_id_count - 1]['subject_id']);

			for($i = 0; $i < $subject_id_count; $i++)
			{
				$this->dbInstance->InsertSubject($unique_subject_ids[$i]['subject_id'], $this->sourceId, $unique_subject_ids[$i]['file_id'], $this->sourceUID);
				unset($unique_subject_ids[$i]);
				$this->processedRecords++;

				$this->reportProgress($this->sourceId, $this->processedRecords, $this->totalRecords);
			}

			$this->dbInstance->commitTransaction(true);

			$offset += $batchSize;
		}
	}

	private function createRelationships(array $attribute_ids)
	{
		$offset = 0;
		$currId = 0;
		$batchSize = NEO4J_BATCH_SIZE;

		while ($offset < $this->totalEAVsCount) {

			$eavdata = $this->eavModel->getEAVsBySourceIdAndAttributeIds($this->sourceId, $attribute_ids, $batchSize, $currId, $this->append);
			$eav_count = count($eavdata);
			if ($eav_count == 0){
				break;
			}
			$lastRec = end($eavdata);
			$currId = $lastRec['id'];
			reset($eavdata);

			for ($i = 0; $i < $eav_count; $i++) {
				$subject_id = $eavdata[$i]['subject_id'];
				$attribute_id = $eavdata[$i]['attribute_id'];
				$value_id = $eavdata[$i]['value_id'];
				$attribute = $this->getAttributeById($attribute_id);
				$value_name = $this->getValueNameByIdAndAttributeId($value_id, $attribute_id);

				if ($attribute != null && $attribute['type'] == ATTRIBUTE_TYPE_ONTOLOGY_TERM){
					$ontologyAssociations = $this->getOntologyAssociationsByAttributeId($attribute_id);
					foreach ($ontologyAssociations as $ontologyAssoctiaion) {
						$prefix = $this->getOntologyPrefixById($ontologyAssoctiaion['prefix_id']);

						if (str_starts_with($value_name, strtolower($prefix))){
							$ontology = $this->getOntologyById($ontologyAssoctiaion['ontology_id']);
							$relationship = $this->getOntologyRelationshipById($ontologyAssoctiaion['relationship_id']);
							$ontology_term = str_replace(strtolower($prefix), $ontology['key_prefix'], $value_name);
							$this->dbInstance->ConnectSubject($subject_id, $ontology['node_type'], $ontology['node_key'], $ontology_term, $relationship);
						}
					}
				}
				unset($eavdata[$i]);
				$this->processedRecords++;
			}
			$this->dbInstance->commitTransaction(true);
			$this->reportProgress($this->sourceId, $this->processedRecords, $this->totalRecords);
		}
	}

	protected function getOntologyAssociationsByAttributeId(int $attribute_id): ?array
	{
		if(array_key_exists($attribute_id, $this->ontologyAssociations)){
			return $this->ontologyAssociations[$attribute_id];
		}
		else{
			$ontologyAssociations = $this->attributeModel->getOntologyAssociationsByAttributeId($attribute_id);
			$this->ontologyAssociations[$attribute_id] = $ontologyAssociations;
			return $ontologyAssociations;
		}
		return null;
	}

	protected function getOntologyById(int $ontology_id): ?array
	{
		if (array_key_exists($ontology_id, $this->ontologies)){
			return $this->ontologies[$ontology_id];
		}
		else{
			$ontology = $this->ontologyModel->getOntology($ontology_id);
			$this->ontologies[$ontology_id] = $ontology;
			return $ontology;
		}
		return null;
	}

	protected function getOntologyPrefixById(int $prefix_id): ?string
	{
		if (array_key_exists($prefix_id, $this->ontologyPrefixes)){
			return $this->ontologyPrefixes[$prefix_id];
		}
		else{
			$prefix = $this->prefixModel->getOntologyPrefix($prefix_id);
			$this->ontologyPrefixes[$prefix_id] = $prefix['name'];
			return $prefix['name'];
		}
		return null;
	}

	protected function getOntologyRelationshipById(int $relationship_id): ?string
	{
		if (array_key_exists($relationship_id, $this->ontologyRelationships)){
			return $this->ontologyRelationships[$relationship_id];
		}
		else{
			$relationship = $this->relationshipModel->getOntologyRelationship($relationship_id);
			$this->ontologyRelationships[$relationship_id] = $relationship['name'];
			return $relationship['name'];
		}
		return null;
	}
}
