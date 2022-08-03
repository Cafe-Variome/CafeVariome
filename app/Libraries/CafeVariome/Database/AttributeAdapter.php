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
	protected string $table = 'attributes';

	/**
	 * @inheritDoc
	 */
	protected string $key = 'id';

	public function ReadIdByNameAndSourceId(string $name, int $source_id): ?int
	{
		$this->builder->select($this->GetKey());
		$this->builder->where('name', $name);
		$this->builder->where('source_id', $source_id);
		$results = $this->builder->get()->getResult();

		if (count($results) == 1)
		{
			return $results[0]->{$this->GetKey()};
		}

		return null;
	}

	public function ReadType(int $id): ?int
	{
		$this->builder->select('type');
		$this->builder->where($this->GetKey(), $id);

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
		$this->builder->where($this->GetKey(), $id);

		$result = $this->builder->get()->getResult();

		if (count($result) == 1)
		{
			return [$result[0]->min, $result[0]->max];
		}

		return [];
	}


	public function UpdateType(int $id, int $type): bool
	{
		$this->builder->where($this->GetKey(), $id);
		return $this->builder->update(['type' => $type]);
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
