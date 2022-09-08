<?php namespace App\Libraries\CafeVariome\Database;

/**
 * OntologyAdapter.php
 * Created 18/08/2022
 *
 * This class offers CRUD operation for Ontology.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\OntologyFactory;

class OntologyAdapter extends BaseAdapter
{

	/**
	 * @inheritDoc
	 */
	protected static string $table = 'ontologies';

	/**
	 * @inheritDoc
	 */
	protected static string $key = 'id';

	public function ReadOntologyPrefixIdsAndRelationshipIdsByAttributeId(int $attribute_id): array
	{
		$this->changeTable('attributes_ontology_prefixes_relationships');
		$this->builder->select('prefix_id, relationship_id');
		$this->builder->where('attribute_id', $attribute_id);

		$result = $this->builder->get()->getResult();
		$this->resetTable();

		return $result;
	}


	public function CreateOntologyAttributeAssociation(int $attribute_id, int $prefix_id, int $relationship_id, int $ontology_id): int
	{
		$this->changeTable('attributes_ontology_prefixes_relationships');

		$insert_id = $this->builder->insert([
			'attribute_id' => $attribute_id,
			'prefix_id' => $prefix_id,
			'relationship_id' => $relationship_id,
			'ontology_id' => $ontology_id
		]);
		$this->resetTable();

		return $insert_id;
	}

	/**
     * @inheritDoc
     */
    public function toEntity(?object $object): IEntity
    {
        $ontologyFactory = new OntologyFactory();
		return $ontologyFactory->GetInstance($object);
    }
}
