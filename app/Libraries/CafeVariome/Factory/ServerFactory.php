<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * ServerFactory.php
 * Created 25/04/2022
 *
 * This class handles object creation of the Server class.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\Server;

class ServerFactory extends EntityFactory
{

	/**
	 * @param object|null $input
	 * @return IEntity
	 * @throws \Exception
	 */
	public function getInstance(?object $input): IEntity
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

		return new Server($properties);
	}

	/**
	 * Creates and returns an object of type Server based on input parameters.
	 *
	 * @param string $name
	 * @param string $address
	 * @param bool $removable
	 * @return Server
	 * @throws \Exception
	 */
	public function getInstanceFromParameters(string $name, string $address, bool $removable = true): Server
	{
		return new Server(['name' => $name, 'address' => $address, 'removable' => $removable]);
	}
}
