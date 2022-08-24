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
	protected static string $table = 'tasks';

	/**
	 * @inheritDoc
	 */
	protected static string $key = 'id';

	public function ReadLastProcessingTaskIdBySourceIdAndType(int $source_id, int $task_type): ?int
	{
		$this->builder->select(static::$table . '.' . static::$key);
		$this->builder->where(static::$table . '.source_id', $source_id);
		$this->builder->where(static::$table . '.type', $task_type);
		$this->builder->where(static::$table . '.status', TASK_STATUS_PROCESSING);
		$this->builder->limit(1);
		$this->builder->orderBy(static::$table . '.' . static::$key, 'DESC');


		$result = $this->builder->get()->getResult();

		if (count($result) == 1)
		{
			return $result[0]->id;
		}
		return null;
	}

    /**
     * @inheritDoc
     */
    public function toEntity(?object $object): IEntity
    {
        $taskFactory = new TaskFactory();
		return $taskFactory->GetInstance($object);
    }
}
