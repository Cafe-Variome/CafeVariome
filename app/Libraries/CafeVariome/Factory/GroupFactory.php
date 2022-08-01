<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * GroupFactory.php
 * Created 27/07/2022
 *
 * This class handles object creation of the Group class.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\Group;
use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\NullEntity;

class GroupFactory extends EntityFactory
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

		return new Group($properties);
	}

	public function GetInstanceFromParameters(string $name, int $source_id, string $display_name): IEntity
	{
		return new Group([
			'name' => $name,
			'source_id' => $source_id,
			'display_name' => $display_name
		]);
	}
}
