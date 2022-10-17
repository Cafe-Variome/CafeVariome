<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * AttributeFactory.php
 * Created 28/07/2022
 *
 * This class handles object creation of the Attribute class.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\Attribute;
use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\NullEntity;

class AttributeFactory extends EntityFactory
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

		return new Attribute($properties);
	}

	public function GetInstanceFromParameters(
		string $name,
		int $source_id,
		string $display_name,
		int $type = ATTRIBUTE_TYPE_UNDEFINED,
		float $min = 0.0,
		float $max = 0.0,
		bool $show_in_interface = true,
		bool $include_in_interface_index = true,
		int $storage_location = ATTRIBUTE_STORAGE_UNDEFINED
	): IEntity
	{
		return new Attribute([
			'name' => $name,
			'source_id' => $source_id,
			'display_name' => $display_name,
			'type' => $type,
			'min' => $min,
			'max' => $max,
			'show_in_interface' => $show_in_interface,
			'include_in_interface_index' => $include_in_interface_index,
			'storage_location' => $storage_location
		]);
	}
}
