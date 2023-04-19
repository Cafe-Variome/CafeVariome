<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * PipelineFactory.php
 * Created 30/05/2022
 *
 * This class handles object creation of the Pipeline class.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\Pipeline;

class PipelineFactory extends EntityFactory
{
	/**
	 * @param object|null $input
	 * @return IEntity
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

		return new Pipeline($properties);
	}

	public function GetInstanceFromParameters(
		string $name,
		int $subject_id_location,
		string $subject_id_attribute_name,
		string  $subject_id_prefix,
		int $subject_id_assignment_batch_size,
		?int $expansion_policy,
		string $expansion_columns,
		?string $expansion_attribute_name,
		int $grouping,
		?string $group_columns,
		?string $internal_delimiter
	): IEntity
	{
		return new Pipeline([
			'name' => $name,
			'subject_id_location' => $subject_id_location,
			'subject_id_attribute_name' => $subject_id_attribute_name,
			'subject_id_prefix' => $subject_id_prefix,
			'subject_id_assignment_batch_size' => $subject_id_assignment_batch_size,
			'expansion_policy' => $expansion_policy,
			'expansion_columns' => $expansion_columns,
			'expansion_attribute_name' => $expansion_attribute_name,
			'grouping' => $grouping,
			'group_columns' => $group_columns,
			'internal_delimiter' => $internal_delimiter

		]);
	}
}
