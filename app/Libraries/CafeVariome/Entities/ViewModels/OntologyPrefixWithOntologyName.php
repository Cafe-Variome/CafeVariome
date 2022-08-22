<?php namespace App\Libraries\CafeVariome\Entities\ViewModels;

/**
 * OntologyPrefixWithOntologyName.php
 * Created 22/08/2022
 *
 * @author Mehdi Mehtarizadeh
 */

class OntologyPrefixWithOntologyName extends BaseViewModel
{
	public string $name;

	public int $ontology_id;

	public string $ontology_name;
}
