<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * DiscoveryGroupFactory.php
 * Created 06/09/2022
 *
 * This class handles object creation of the DiscoveryGroup class.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\DiscoveryGroup;
use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\NullEntity;

class DiscoveryGroupFactory extends EntityFactory
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

		return new DiscoveryGroup($properties);
	}

	public function GetInstanceFromParameters(string $name,	string $description, int $network_id, int $policy): IEntity
	{
		return new DiscoveryGroup([
			'name' => $name,
			'description' => $description,
			'network_id' => $network_id,
			'policy' => $policy
		]);
	}
}
