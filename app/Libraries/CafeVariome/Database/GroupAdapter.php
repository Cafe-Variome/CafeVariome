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
