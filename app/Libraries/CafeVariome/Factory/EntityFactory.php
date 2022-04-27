<?php namespace App\Libraries\CafeVariome\Factory;

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\Entity;
use App\Libraries\CafeVariome\Entities\NullEntity;

/**
 * EntityFactory.php
 * Created 25/04/2022
 *
 * This is a base factory class for handling object creation of Entity classes. It might become an abstract class in the future.
 * @author Mehdi Mehtarizadeh
 */

class EntityFactory
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

		return new Entity($properties);
	}
}
