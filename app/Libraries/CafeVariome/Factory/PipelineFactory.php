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
}
