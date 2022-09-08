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

	public function ReadOntologyPrefixesAndRelationshipsByAttributeId(int $attribute_id): array
	{
		$this->changeTable('attributes_ontology_prefixes_relationships as aop');

		$this->builder->select('aop.id as id, ontologies.name as ontology_name, ontology_prefixes.name as prefix_name, ontology_relationships.name as relationship_name');
		$this->builder->where('aop.attribute_id', $attribute_id);
		$this->builder->join('ontologies', 'aop.ontology_id = ontologies.id');
		$this->builder->join('ontology_prefixes', 'aop.prefix_id = ontology_prefixes.id');
		$this->builder->join('ontology_relationships', 'aop.relationship_id = ontology_relationships.id');

		$result = $this->builder->get()->getResult();
		$this->resetTable();

		return $result;
	}

	public function ReadAttributeOntologyAssociation(int $association_id)
	{
		$this->changeTable('attributes_ontology_prefixes_relationships as aop');
		$this->builder->select('aop.id as id, ontologies.name as ontology_name, ontology_prefixes.name as prefix_name, ontology_relationships.name as relationship_name, ' . AttributeAdapter::GetTable() . '.name as attribute_name, '. AttributeAdapter::GetTable() . '.' . AttributeAdapter::GetKey() . ' as attribute_id');
		$this->builder->where('aop.id', $association_id);
		$this->builder->join(static::$table, 'aop.ontology_id = '. static::$table . '.' .static::$key);
		$this->builder->join(AttributeAdapter::GetTable(), 'aop.attribute_id = ' . AttributeAdapter::GetTable() . '.' . AttributeAdapter::GetKey());
		$this->builder->join('ontology_prefixes', 'aop.prefix_id = ontology_prefixes.id');
		$this->builder->join('ontology_relationships', 'aop.relationship_id = ontology_relationships.id');
		$result = $this->builder->get()->getResultArray();
		$this->resetTable();

		return count($result) == 1 ? $result[0] : null;
	}

	public function DeleteAttributeOntologyAssociation(int $association_id)
	{
		$this->changeTable('attributes_ontology_prefixes_relationships');
		$this->builder->where('id', $association_id);
		$this->builder->delete();
		$this->resetTable();
	}

	public function ReadOntologyAssociationsByAttributeId(int $attribute_id): array
	{
		$this->changeTable('attributes_ontology_prefixes_relationships');
		$this->builder->where('attribute_id', $attribute_id);
		$result = $this->builder->get()->getResult();
		$this->resetTable();

		return $result;
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
