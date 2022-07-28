<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * SourceFactory.php
 * Created 21/06/2022
 *
 * This class handles object creation of the Source class.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\Source;

class SourceFactory extends EntityFactory
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

		return new Source($properties);
	}

	public function GetInstanceFromParameters(
		string $name,
		string $uid,
		string $display_name,
		string $description,
		string $owner_name,
		string $owner_email,
		string $uri,
		int $date_created,
		int $record_count,
		bool $locked,
		bool $status
	)
	{
		return new Source([
			'name' => $name,
			'uid' => $uid,
			'display_name' => $display_name,
			'description' => $description,
			'owner_name' => $owner_name,
			'owner_email' => $owner_email,
			'uri' => $uri,
			'date_created' => $date_created,
			'record_count' => $record_count,
			'locked' => $locked,
			'status' => $status
		]);
	}
}
