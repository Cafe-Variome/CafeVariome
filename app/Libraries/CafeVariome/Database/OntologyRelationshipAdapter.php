<?php namespace App\Libraries\CafeVariome\Database;

/**
 * OntologyRelationshipAdapter.php
 * Created 22/08/2022
 *
 * This class offers CRUD operation for OntologyRelationship.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\OntologyRelationshipFactory;

class OntologyRelationshipAdapter extends BaseAdapter
{
	/**
	 * @inheritdoc
	 */
	protected static string $table = 'ontology_relationships';

	/**
	 * @inheritdoc
	 */
	protected static string $key = 'id';

	public function ReadByOntologyId(int $ontology_id): array
	{
		$this->CompileSelect();
		$this->CompileJoin();
		$this->builder->where(static::$table . '.ontology_id', $ontology_id);
		$results = $this->builder->get()->getResult();

		$entities = [];
		for($c = 0; $c < count($results); $c++)
		{
			$entities[$results[$c]->{static::$key}] = $this->binding != null ? $this->BindTo($results[$c]) : $this->toEntity($results[$c]);
		}

		return $entities;
	}

	public function ReadByNameAndOntologyId(string $name, int $ontology_id): IEntity
	{
		$this->CompileSelect();
		$this->CompileJoin();
		$this->builder->where(static::$table . '.name', $name);
		$this->builder->where(static::$table . '.ontology_id', $ontology_id);

		$results = $this->builder->get()->getResult();

		$record = null;
		if (count($results) == 1)
		{
			$record = $results[0];
		}

		return $this->binding != null ? $this->BindTo($record) : $this->toEntity($record);
	}

    /**
     * @inheritDoc
     */
    public function toEntity(?object $object): IEntity
    {
        $ontologyRelationshipFactory = new OntologyRelationshipFactory();
		return $ontologyRelationshipFactory->GetInstance($object);
    }
}
