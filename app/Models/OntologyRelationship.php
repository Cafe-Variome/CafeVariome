<?php namespace App\Models;

/**
 * Name OntologyRelationship.php
 * @author Mehdi Mehtarizadeh
 *
 * Model class for OntologyRelationship entities.
 */

use \CodeIgniter\Model;

class OntologyRelationship extends Model
{
	protected $db;
	protected $table      = 'ontology_relationships';
	protected $builder;

	protected $primaryKey = 'id';

	public function __construct()
	{
		$this->db = \Config\Database::connect();
		$this->builder = $this->db->table($this->table);
	}

	public function createOntologyRelationship(string $name, int $ontology_id): int
	{
		return $this->builder->insert([
			'name' => $name,
			'ontology_id' => $ontology_id
		]);
	}

	public function getOntologyRelationships(int $ontology_id)
	{
		$this->builder->select();
		$this->builder->where('ontology_id', $ontology_id);

		return $this->builder->get()->getResultArray();
	}

	public function getOntologyRelationship(int $id)
	{
		$this->builder->select();
		$this->builder->where('id', $id);

		$result =  $this->builder->get()->getResultArray();

		if (count($result) == 1){
			return $result[0];
		}

		return null;
	}

	public function ontologyRelationshipExists(string $name, int $ontology_id, int $relationship_id = -1): bool
	{
		$this->builder->select();
		$this->builder->where('name', $name);
		$this->builder->where('ontology_id', $ontology_id);
		if ($relationship_id > 0){
			$this->builder->where('id!=', $relationship_id);
		}

		$result =  $this->builder->get()->getResultArray();

		return count($result) > 0;
	}

	public function updateOntologyRelationship(int $id, string $name): bool
	{
		$this->builder->where('id', $id);
		return $this->builder->update([
			'name' => $name,
		]);
	}

	public function deleteOntologyRelationship(int $id)
	{
		$this->builder->where('id', $id);
		$this->builder->delete();
	}
}
