<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * AttributeMappingFactory.php
 * Created 15/12/2022
 *
 * This class handles object creation of the AttributeMapping class.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\AttributeMapping;
use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\NullEntity;

class AttributeMappingFactory extends EntityFactory
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

		return new AttributeMapping($properties);
	}

	public function GetInstanceFromParameters(string $name, int $attribute_id)
	{
		return new AttributeMapping([
			'name' => $name,
			'attribute_id' => $attribute_id
		]);
	}
}
