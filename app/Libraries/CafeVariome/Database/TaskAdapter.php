<?php namespace App\Libraries\CafeVariome\Database;

/**
 * TaskAdapter.php
 * Created 05/07/2022
 *
 * This class offers CRUD operation for Task.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\TaskFactory;

class TaskAdapter extends BaseAdapter
{
	/**
	 * @inheritDoc
	 */
	protected string $table = 'tasks';

	/**
	 * @inheritDoc
	 */
	protected string $key = 'id';

    /**
     * @inheritDoc
     */
    public function toEntity(?object $object): IEntity
    {
        $taskFactory = new TaskFactory();
		return $taskFactory->GetInstance($object);
    }
}
