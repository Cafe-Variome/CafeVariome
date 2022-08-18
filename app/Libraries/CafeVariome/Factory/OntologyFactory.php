<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * OntologyFactory.php
 * Created 18/08/2022
 *
 * This class handles object creation of the Ontology class.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\Ontology;

class OntologyFactory extends EntityFactory
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

		return new Ontology($properties);
	}

	public function GetInstanceFromParameters(string $name, string $description, string $node_key, string $node_type, string $key_prefix, string $term_name): Ontology
	{
		return new Ontology([
			'name' => $name,
			'description' => $description,
			'node_key' => $node_key,
			'node_type' => $node_type,
			'key_prefix' => $key_prefix,
			'term_name' => $term_name
		]);
	}
}
