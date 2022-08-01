<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * ValueFactory.php
 * Created 28/07/2022
 *
 * This class handles object creation of the Value class.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\Value;

class ValueFactory extends EntityFactory
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

		return new Value($properties);
	}

	public function GetInstanceFromParameters(
		string $name,
		int $attribute_id,
		string $display_name,
		int $frequency = 0,
		bool $show_in_interface = true,
		bool $include_in_interface_index = true
	): IEntity
	{
		return new Value([
			'name' => $name,
			'attribute_id' => $attribute_id,
			'display_name' => $display_name,
			'frequency' => $frequency,
			'show_in_interface' => $show_in_interface,
			'include_in_interface_index' => $include_in_interface_index
		]);
	}
}
