<?php namespace App\Libraries\CafeVariome\Database;

/**
 * AttributeAdapter.php
 * Created 28/07/2022
 *
 * This class offers CRUD operation for Attribute.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\AttributeFactory;

class AttributeAdapter extends BaseAdapter
{

	/**
	 * @inheritDoc
	 */
	protected static string $table = 'attributes';

	/**
	 * @inheritDoc
	 */
	protected static string $key = 'id';

	public function ReadIdByNameAndSourceId(string $name, int $source_id): ?int
	{
		$this->builder->select(static::$key);
		$this->builder->where('name', $name);
		$this->builder->where('source_id', $source_id);
		$results = $this->builder->get()->getResult();

		if (count($results) == 1)
		{
			return $results[0]->{static::$key};
		}

		return null;
	}

	public function ReadType(int $id): ?int
	{
		$this->builder->select('type');
		$this->builder->where(static::$key, $id);

		$result = $this->builder->get()->getResult();

		if (count($result) == 1)
		{
			return $result[0]->type;
		}

		return null;
	}

	public function ReadMinimumAndMaximum(int $id): array
	{
		$this->builder->select('min, max');
		$this->builder->where(static::$key, $id);

		$result = $this->builder->get()->getResult();

		if (count($result) == 1)
		{
			return [$result[0]->min, $result[0]->max];
		}

		return [];
	}

	public function ReadIdsBySourceIdAndStorageLocation(int $source_id, int $storage_location): array
	{
		$this->builder->select('id');
		$this->builder->where('source_id', $source_id);
		$this->builder->where('storage_location', $storage_location);
		$result = $this->builder->get()->getResult();

		$ids = [];
		foreach ($result as $id)
		{
			array_push($ids, $id->id);
		}

		return $ids;
	}

	public function ReadBySourceId(int $source_id)
	{
		$this->CompileSelect();
		$this->CompileJoin();
		$this->builder->where(static::$table . '.source_id', $source_id);

		$results = $this->builder->get()->getResult();

		$entities = [];
		for($c = 0; $c < count($results); $c++)
		{
			$entities[$results[$c]->{static::$key}] = $this->binding != null ? $this->BindTo($results[$c]) : $this->toEntity($results[$c]);
		}

		return $entities;
	}

	public function AssociationExists(int $attribute_id, int $ontology_id, int $prefix_id, int $relationship_id): bool
	{
		$this->changeTable('attributes_ontology_prefixes_relationships as aop');

		$this->builder->where('aop.attribute_id', $attribute_id);
		$this->builder->where('aop.ontology_id', $ontology_id);
		$this->builder->where('aop.prefix_id', $prefix_id);
		$this->builder->where('aop.relationship_id', $relationship_id);

		$result = $this->builder->get()->getResult();

		$this->resetTable();

		return count($result) == 1;
	}
	
	public function CreateOntologyAssociation(int $attribute_id, int $prefix_id, int $relationship_id, int $ontology_id): int
	{
		$this->changeTable('attributes_ontology_prefixes_relationships');

		$association_id = $this->builder->insert([
			'attribute_id' => $attribute_id,
			'prefix_id' => $prefix_id,
			'relationship_id' => $relationship_id,
			'ontology_id' => $ontology_id
		]);

		$this->resetTable();

		return $association_id;
	}

	public function UpdateType(int $id, int $type): bool
	{
		$this->builder->where(static::$key, $id);
		return $this->builder->update(['type' => $type]);
	}

	public function UpdateStorageLocation(int $id, int $storage_location): bool
	{
		$this->builder->where(static::$key, $id);
		return $this->builder->update(['storage_location' => $storage_location]);
	}

	public function UpdateMinimumAndMaximum(int $id, float $minimum, float $maximum): bool
	{
		$this->builder->where(static::$key, $id);
		return $this->builder->update([
			'min' => $minimum,
			'max' => $maximum
		]);
	}

	/**
    /**
     * @inheritDoc
     */
    public function toEntity(?object $object): IEntity
    {
        $attributeFactory = new AttributeFactory();
		return $attributeFactory->GetInstance($object);
    }
}
