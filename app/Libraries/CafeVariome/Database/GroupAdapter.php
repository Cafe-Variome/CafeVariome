<?php namespace App\Libraries\CafeVariome\Database;

/**
 * GroupAdapter.php
 * Created 27/07/2022
 *
 * This class offers CRUD operation for Group.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\GroupFactory;

class GroupAdapter extends BaseAdapter
{
	/**
	 * @inheritDoc
	 */
	protected static string $table = 'groups';

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

	public function AddAttributes(int $id, array $attribute_ids)
	{
		$this->changeTable('attributes_groups');
		$batch = [];
		for($c = 0; $c < count($attribute_ids); $c++)
		{
			array_push($batch, ['attribute_id' => $attribute_ids[$c], 'group_id' => $id]);
		}
		if(count($batch) > 0)
		{
			$this->builder->insertBatch($batch);
		}
		$this->resetTable();
	}

	public function ReadAttributeIds(int $id): array
	{
		$this->changeTable('attributes_groups');
		$this->builder->where('group_id', $id);
		$results = $this->builder->get()->getResult();

		$attribute_ids = [];

		for($c = 0; $c < count($results); $c++)
		{
			array_push($attribute_ids, $results[$c]->attribute_id);
		}

		$this->resetTable();

		return $attribute_ids;
	}

	/**
	 * Converts general PHP objects to a Group object.
	 * @param object|null $object
	 * @return IEntity
	 * @throws \Exception
	 */
    public function toEntity(?object $object): IEntity
    {
		$groupFactory = new GroupFactory();
		return $groupFactory->GetInstance($object);
    }
}
