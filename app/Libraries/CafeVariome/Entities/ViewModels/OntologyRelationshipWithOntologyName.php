<?php namespace App\Libraries\CafeVariome\Entities\ViewModels;

/**
 * OntologyRelationshipWithOntologyName.php
 * Created 22/08/2022
 *
 * @author Mehdi Mehtarizadeh
 */

class OntologyRelationshipWithOntologyName extends BaseViewModel
{
	public string $name;

	public int $ontology_id;

	public string $ontology_name;
}
