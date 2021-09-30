<?php namespace App\Models;

/**
 * Name OntologyPrefix.php
 * @author Mehdi Mehtarizadeh
 *
 * Model class for OntologyPrefix entities.
 */

use \CodeIgniter\Model;

class OntologyPrefix extends Model
{
	protected $db;
	protected $table      = 'ontology_prefixes';
	protected $builder;

	protected $primaryKey = 'id';

	public function __construct()
	{
		$this->db = \Config\Database::connect();
		$this->builder = $this->db->table($this->table);
	}

	public function createOntologyPrefix(string $name, int $ontology_id): int
	{
		return $this->builder->insert([
			'name' => $name,
			'ontology_id' => $ontology_id
		]);
	}

	public function getOntologyPrefixes(int $ontology_id)
	{
		$this->builder->select();
		$this->builder->where('ontology_id', $ontology_id);

		return $this->builder->get()->getResultArray();
	}

	public function getOntologyPrefix(int $id)
	{
		$this->builder->select();
		$this->builder->where('id', $id);

		$result =  $this->builder->get()->getResultArray();

		if (count($result) == 1){
			return $result[0];
		}

		return null;
	}

	public function ontologyPrefixExists(string $name, int $ontology_id, int $prefix_id = -1): bool
	{
		$this->builder->select();
		$this->builder->where('name', $name);
		$this->builder->where('ontology_id', $ontology_id);
		if ($prefix_id > 0){
			$this->builder->where('id!=', $prefix_id);
		}

		$result =  $this->builder->get()->getResultArray();

		return count($result) > 0;
	}

	public function updateOntologyPrefix(int $id, string $name): bool
	{
		$this->builder->where('id', $id);
		return $this->builder->update([
			'name' => $name,
		]);
	}

	public function deleteOntologyPrefix(int $id)
	{
		$this->builder->where('id', $id);
		$this->builder->delete();
	}

}
