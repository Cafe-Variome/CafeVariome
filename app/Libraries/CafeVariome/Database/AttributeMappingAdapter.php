<?php namespace App\Libraries\CafeVariome\Database;

/**
 * AttributeMappingAdapter.php
 * Created 15/12/2022
 *
 * This class offers CRUD operation for AttributeMapping.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\AttributeMappingFactory;

class AttributeMappingAdapter extends BaseAdapter
{
	/**
	 * @inheritdoc
	 */
	protected static string $key = 'id';

	/**
	 * @inheritdoc
	 */
	protected static string $table = 'attribute_mappings';

	public function ReadByAttributeId(int $attribute_id): array
	{
		$this->CompileSelect();
		$this->CompileJoin();
		$this->builder->where(static::$table . '.attribute_id', $attribute_id);
		$results = $this->builder->get()->getResult();

		$entities = [];
		for($c = 0; $c < count($results); $c++)
		{
			$entities[$results[$c]->{static::$key}] = $this->binding != null ? $this->BindTo($results[$c]) : $this->toEntity($results[$c]);
		}

		return $entities;
	}

	public function ExistsWithinSource(string $name, int $source_id): bool
	{
		$this->builder->select(static::$table . '.name');
		$this->builder->where(static::$table . '.name', $name);
		$this->builder->join(AttributeAdapter::GetTable(), static::$table . '.attribute_id = ' . AttributeAdapter::GetTable() . '.id');
		$this->builder->where(AttributeAdapter::GetTable() . '.source_id', $source_id);
		$result = $this->builder->get()->getResult();

		return count($result) > 0;
	}

    /**
     * @inheritDoc
     */
    public function toEntity(?object $object): IEntity
    {
        $attributeMappingFactory = new AttributeMappingFactory();
		return $attributeMappingFactory->GetInstance($object);
    }
}
