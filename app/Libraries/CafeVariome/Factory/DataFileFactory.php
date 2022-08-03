<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * DataFileFactory.php
 * Created 17/05/2022
 *
 * This class handles object creation of the DataFile class.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\DataFile;
use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\NullEntity;

class DataFileFactory extends EntityFactory
{
	/**
	 * @param object|null $input
	 * @return IEntity
	 * @throws \Exception
	 */
	public function GetInstance(?object $input): IEntity
	{
		if (is_null($input) || count($objectVars = get_object_vars($input)) == 0 )
		{
			return new NullEntity();
		}

		$properties = [];
		foreach ($objectVars as $var => $value)
		{
			$properties[$var] = $value;
		}

		return new DataFile($properties);
	}

	public function GetInstanceFromParameters(
		string $name,
		string $disk_name,
		float $size,
		int $upload_date,
		int $record_count,
		int $user_id,
		int $source_id,
		int $status
	): IEntity
	{
		return new DataFile([
			'name' => $name,
			'disk_name' => $disk_name,
			'size' => $size,
			'upload_date' => $upload_date,
			'record_count' => $record_count,
			'user_id' => $user_id,
			'source_id' => $source_id,
			'status' => $status
		]);
	}
}
