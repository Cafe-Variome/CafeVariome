<?php namespace App\Libraries\CafeVariome\Entities;

/**
 * OntologyRelationship.php
 * Created 22/08/2022
 *
 * This class extends Entity and implements IEntity.
 * @author Mehdi Mehtarizadeh
 */

class OntologyRelationship extends Entity
{
	public string $name;

	public int $ontology_id;
}
