<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Index;

use App\Libraries\CafeVariome\Database\OntologyAdapter;
use App\Libraries\CafeVariome\Database\OntologyPrefixAdapter;
use App\Libraries\CafeVariome\Database\OntologyRelationshipAdapter;
use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\Task;
use App\Libraries\CafeVariome\Factory\OntologyAdapterFactory;
use App\Libraries\CafeVariome\Factory\OntologyPrefixAdapterFactory;
use App\Libraries\CafeVariome\Factory\OntologyRelationshipAdapterFactory;

/**
 * Name Neo4JDataIndex.php
 *
 * Created 06/10/2021
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 *
 */

class Neo4JSourceIndex extends AbstractSourceIndex
{
	private OntologyAdapter $ontologyAdapter;
	private OntologyPrefixAdapter $ontologyPrefixAdapter;
	private OntologyRelationshipAdapter $ontologyRelationshipAdapter;

	private array $ontologies;
	private array $ontologyPrefixes;
	private array $ontologyRelationships;
	private array $ontologyAssociations;

	public function __construct(Task $task)
	{
		parent::__construct($task->source_id);
		$this->continue = true;
		$this->overwrite = $task->overwrite;
		$this->taskId = $task->getID();

		$this->dbInstance = new Neo4J();
		$this->processedRecords = 0;
		$this->totalRecords = 0;
		$this->totalEAVsCount = 0;
		$this->ontologyAdapter = (new OntologyAdapterFactory())->GetInstance();
		$this->ontologyPrefixAdapter = (new OntologyPrefixAdapterFactory())->GetInstance();
		$this->ontologyRelationshipAdapter = (new OntologyRelationshipAdapterFactory())->GetInstance();
		$this->ontologies = [];
		$this->ontologyPrefixes = [];
		$this->ontologyRelationships = [];
		$this->ontologyAssociations = [];
	}

    public function IndexSource()
    {
		if ($this->overwrite)
		{
			$this->dbInstance->deleteSource($this->sourceId);
		}

		//Get attribute Ids that must be stored on Elasticsearch
		$attribute_ids = $this->attributeAdapter->ReadIdsBySourceIdAndStorageLocation($this->sourceId, ATTRIBUTE_STORAGE_NEO4J);

		$this->sourceAdapter->Lock($this->sourceId);

		$this->createSubjectNodes($attribute_ids);
		$this->createRelationships($attribute_ids);

		$this->EAVAdapter->UpdateIndexedBySourceIdAndAttributeIds($this->sourceId, $attribute_ids);

		$this->sourceAdapter->Unlock($this->sourceId);

		$this->ReportProgress(100, 'Finished', true);
    }

	private function createSubjectNodes(array $attribute_ids)
	{
		$offset = 0;
		$currId = 0;
		$batchSize = NEO4J_BATCH_SIZE;

		// Get total EAV records by source_id (Neo4J relationships)
		$this->totalEAVsCount = $this->EAVAdapter->CountBySourceIdAndAttributeIds($this->sourceId, $attribute_ids, !$this->overwrite);
		// Get unique subject_ids by source_id
		$uniqueSubjectIdsCount = $this->EAVAdapter->CountUniqueSubjectIdsBySourceIdAndAttributeIds($this->sourceId, $attribute_ids, !$this->overwrite);
		//Total number of records to be created.
		$this->totalRecords = $this->totalEAVsCount + $uniqueSubjectIdsCount;

		$this->ReportProgress(0, 'Inserting subjects into Neo4J');

		while ($offset < $uniqueSubjectIdsCount)
		{
			$unique_subject_ids = $this->EAVAdapter->ReadUniqueSubjectIdsAndFileIdsBySourceIdAndAttributeIds($this->sourceId, $attribute_ids, $batchSize, $currId, !$this->overwrite);
			$subject_id_count = count($unique_subject_ids);

			if ($subject_id_count == 0)
			{
				break;
			}

			$currId = $this->EAVAdapter->ReadLastIdBySubjectId($unique_subject_ids[$subject_id_count - 1]->subject_id);

			for($i = 0; $i < $subject_id_count; $i++)
			{
				$subject_id = $this->getSubjectById($unique_subject_ids[$i]->subject_id);
				$this->dbInstance->InsertSubject($subject_id, $this->sourceId, $unique_subject_ids[$i]->data_file_id, $this->sourceUID);
				unset($unique_subject_ids[$i]);
				$this->processedRecords++;

				$this->ReportProgress();
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

		while ($offset < $this->totalEAVsCount)
		{
			$eavdata = $this->EAVAdapter->ReadBySourceIdAndAttributeIds($this->sourceId, $attribute_ids, $batchSize, $currId, !$this->overwrite);
			$eav_count = count($eavdata);
			if ($eav_count == 0)
			{
				break;
			}
			$lastRec = end($eavdata);
			$currId = $lastRec->id;
			reset($eavdata);

			for ($i = 0; $i < $eav_count; $i++)
			{
				$subject_id = $this->getSubjectById($eavdata[$i]->subject_id);
				$attribute_id = $eavdata[$i]->attribute_id;
				$value_id = $eavdata[$i]->value_id;
				$attribute = $this->getAttributeById($attribute_id);
				$value_name = $this->getValueNameByIdAndAttributeId($value_id, $attribute_id);

				if ($attribute != null && $attribute['type'] == ATTRIBUTE_TYPE_ONTOLOGY_TERM)
				{
					$ontologyAssociations = $this->getOntologyAssociationsByAttributeId($attribute_id);
					foreach ($ontologyAssociations as $ontologyAssoctiaion)
					{
						$prefix = $this->getOntologyPrefixById($ontologyAssoctiaion->prefix_id);

						if (str_starts_with($value_name, strtolower($prefix)))
						{
							$ontology = $this->getOntologyById($ontologyAssoctiaion->ontology_id);
							$relationship = $this->getOntologyRelationshipById($ontologyAssoctiaion->relationship_id);
							$ontology_term = str_replace(strtolower($prefix), $ontology->key_prefix, $value_name);
							$this->dbInstance->ConnectSubject($subject_id, $ontology->node_type, $ontology->node_key, $ontology_term, $relationship);
						}
					}
				}
				unset($eavdata[$i]);
				$this->processedRecords++;
			}

			$this->dbInstance->commitTransaction(true);
			$this->ReportProgress();
		}
	}

	protected function getOntologyAssociationsByAttributeId(int $attribute_id): ?array
	{
		if(array_key_exists($attribute_id, $this->ontologyAssociations))
		{
			return $this->ontologyAssociations[$attribute_id];
		}
		else
		{
			$ontologyAssociations = $this->ontologyAdapter->ReadOntologyAssociationsByAttributeId($attribute_id);
			$this->ontologyAssociations[$attribute_id] = $ontologyAssociations;
			return $ontologyAssociations;
		}

		return null;
	}

	protected function getOntologyById(int $ontology_id): IEntity
	{
		if (array_key_exists($ontology_id, $this->ontologies))
		{
			return $this->ontologies[$ontology_id];
		}
		else
		{
			$ontology = $this->ontologyAdapter->Read($ontology_id);
			$this->ontologies[$ontology_id] = $ontology;
			return $ontology;
		}
	}

	protected function getOntologyPrefixById(int $prefix_id): ?string
	{
		if (array_key_exists($prefix_id, $this->ontologyPrefixes))
		{
			return $this->ontologyPrefixes[$prefix_id];
		}
		else
		{
			$prefix = $this->ontologyPrefixAdapter->Read($prefix_id);
			$this->ontologyPrefixes[$prefix_id] = $prefix->name;
			return $prefix->name;
		}

		return null;
	}

	protected function getOntologyRelationshipById(int $relationship_id): ?string
	{
		if (array_key_exists($relationship_id, $this->ontologyRelationships))
		{
			return $this->ontologyRelationships[$relationship_id];
		}
		else
		{
			$relationship = $this->ontologyRelationshipAdapter->Read($relationship_id);
			$this->ontologyRelationships[$relationship_id] = $relationship->name;
			return $relationship->name;
		}

		return null;
	}
}
