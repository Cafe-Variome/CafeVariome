<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * NetworkFactory.php
 * Created 05/09/2022
 *
 * This class handles object creation of the Network class.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\Network;
use App\Libraries\CafeVariome\Entities\NullEntity;

class NetworkFactory extends EntityFactory
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

		return new Network($properties);
	}

	public function GetInstanceFromParameters(int $key, string $name): IEntity
	{
		return new Network(['id'=> $key, 'name' => $name]);
	}
}
