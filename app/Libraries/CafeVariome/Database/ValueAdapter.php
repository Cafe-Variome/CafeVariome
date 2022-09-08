<?php namespace App\Libraries\CafeVariome\Database;

/**
 * ValueAdapter.php
 * Created 28/07/2022
 *
 * This class offers CRUD operation for Value.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\ValueFactory;

class ValueAdapter extends BaseAdapter
{

	/**
	 * @inheritDoc
	 */
	protected static string $table = 'values';

	/**
	 * @inheritDoc
	 */
	protected static string $key = 'id';

	public function CountByAttributeId(int $attribute_id): int
	{
		$this->builder->select(static::$key . ', name, display_name, frequency');
		$this->builder->where('attribute_id', $attribute_id);

		return $this->builder->countAll();
	}

	public function ReadIdByNameAndAttributeId(string $name, int $attribute_id): ?int
	{
		$this->builder->select(static::$key);
		$this->builder->where('name', $name);
		$this->builder->where('attribute_id', $attribute_id);

		$results = $this->builder->get()->getResult();

		if (count($results) == 1)
		{
			return $results[0]->{static::$key};
		}

		return null;
	}

	public function ReadByAttributeId(int $attribute_id, ?bool $include_in_interface_index = null): array
	{
		$this->CompileSelect();
		$this->CompileJoin();
		$this->builder->where(static::$table . '.attribute_id', $attribute_id);
		if (!is_null($include_in_interface_index))
		{
			$this->builder->where(static::$table . '.include_in_interface_index', $include_in_interface_index );
		}

		$results = $this->builder->get()->getResult();

		$entities = [];
		for($c = 0; $c < count($results); $c++)
		{
			$entities[$results[$c]->{static::$key}] = $this->binding != null ? $this->BindTo($results[$c]) : $this->toEntity($results[$c]);
		}

		return $entities;
	}

	public function ReadFrequency(int $id): ?float
	{
		$this->builder->select('frequency');
		$this->builder->where(static::$key, $id);
		$result = $this->builder->get()->getResult();

		if(count($result) == 1)
		{
			return $result[0]->frequency;
		}

		return null;
	}

	public function UpdateFrequency(int $id, float $frequency, bool $add = true): bool
	{
		if ($add)
		{
			$currentFrequency = $this->ReadFrequency($id);
			if (!is_null($currentFrequency))
			{
				$frequency = $frequency + $currentFrequency;
			}
		}
		$this->builder->where(static::$key, $id);

		return $this->builder->update(['frequency' => $frequency]);
	}

	public function DeleteIfAbsent(int $id): bool
	{
		$this->builder->where(static::$key, $id);
		$this->builder->where('frequency', 0); // Frequency = 0 indicates a value is absent and is no more needed.
		return $this->builder->delete();
	}

    /**
     * @inheritDoc
     */
    public function toEntity(?object $object): IEntity
    {
        $valueFactory = new ValueFactory();
		return $valueFactory->GetInstance($object);
    }
}
