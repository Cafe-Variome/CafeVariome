<?php namespace App\Libraries\CafeVariome\Entities;

/**
 * Ontology.php
 * Created 18/08/2022
 *
 * This class extends Entity and implements IEntity.
 * @author Mehdi Mehtarizadeh
 */


class Ontology extends Entity
{
	public string $name;

	public string $description;

	public string $node_key;

	public string $node_type;

	public string $key_prefix;

	public string $term_name;
}
