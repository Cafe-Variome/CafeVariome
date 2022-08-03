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
	protected string $table = 'groups';

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
