<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * OntologyPrefixFactory.php
 * Created 22/08/2022
 *
 * This class handles object creation of the OntologyPrefix class.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\OntologyPrefix;

class OntologyPrefixFactory extends EntityFactory
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

		return new OntologyPrefix($properties);
	}

	public function GetInstanceFromParameters(string $name, int $ontology_id): IEntity
	{
		return new OntologyPrefix([
			'name' => $name,
			'ontology_id' => $ontology_id
		]);
	}
}
