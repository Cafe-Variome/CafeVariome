<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * ValueMappingFactory.php
 * Created 19/12/2022
 *
 * This class handles object creation of the ValueMapping class.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\ValueMapping;

class ValueMappingFactory extends EntityFactory
{
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

		return new ValueMapping($properties);
	}

	public function GetInstanceFromParameters(string $name, int $value_id)
	{
		return new ValueMapping([
			'name' => $name,
			'value_id' => $value_id
		]);
	}
}
