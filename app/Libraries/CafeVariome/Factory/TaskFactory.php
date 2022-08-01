<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * TaskFactory.php
 * Created 05/07/2022
 *
 * This class handles object creation of the Source class.
 * @author Mehdi Mehtarizadeh
 */


use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\Task;

class TaskFactory extends EntityFactory
{
	/**
	 * @param object|null $input
	 * @return IEntity
	 * @throws \Exception
	 */
	public function GetInstance(?object $input): IEntity
	{
		if (is_null($input) || count($objectVars = get_object_vars($input)) == 0)
		{
			return new NullEntity();
		}

		$properties = [];
		foreach ($objectVars as $var => $value)
		{
			$properties[$var] = $value;
		}

		return new Task($properties);
	}

	/**
	 * @param int $user_id
	 * @param int $progress
	 * @param int $status
	 * @param int $error_code
	 * @param string $error_message
	 * @param int|null $started
	 * @param int|null $ended
	 * @param int|null $data_file_id
	 * @param int|null $pipeline_id
	 * @return Task
	 * @throws \Exception
	 */
	public function GetInstanceFromParameters(
		int $user_id,
		int $type,
		int $progress,
		int $status,
		int $error_code,
		?string $error_message,
		?int $started,
		?int $ended,
		?int $data_file_id,
		?int $pipeline_id,
		int $overwrite = UPLOADER_DELETE_FILE
	): Task
	{
		return new Task([
			'user_id' => $user_id,
			'type' => $type,
			'progress' => $progress,
			'status' => $status,
			'error_code' => $error_code,
			'error_message' => $error_message,
			'started' => $started,
			'ended' => $ended,
			'data_file_id' => $data_file_id,
			'pipeline_id' => $pipeline_id,
			'overwrite' => $overwrite
		]);
	}
}
