<?php namespace App\Models;

/**
 * Name Ontology.php
 * @author Mehdi Mehtarizadeh
 *
 * Model class for Ontology entities.
 */

use \CodeIgniter\Model;

class Ontology extends Model
{
	protected $db;
	protected $table      = 'ontologies';
	protected $builder;

	protected $primaryKey = 'id';

	public function __construct()
	{
		$this->db = \Config\Database::connect();
		$this->builder = $this->db->table($this->table);

	}

	public function createOntology(string $name, string $node_key, string $node_type, string $key_prefix, string $term_name, string $description = ''): int
	{
		$this->builder->insert([
			'name' => $name,
			'description' => $description,
			'node_key' => $node_key,
			'node_type' => $node_type,
			'key_prefix' => $key_prefix,
			'term_name' => $term_name
		]);

		return $this->db->insertID();
	}

	public function getOntologies()
	{
		$this->builder->select();
		return $this->builder->get()->getResultArray();
	}

	public function getOntology(int $id)
	{
		$this->builder->select();
		$this->builder->where('id', $id);

		$result =  $this->builder->get()->getResultArray();

		if (count($result) == 1){
			return $result[0];
		}

		return null;
	}

	public function updateOntology(int $id, string $name, string $node_key, string $node_type, string $key_prefix, string $term_name, string $description): bool
	{
		$this->builder->where('id', $id);
		return $this->builder->update([
			'name' => $name,
			'node_key' => $node_key,
			'node_type' => $node_type,
			'key_prefix' => $key_prefix,
			'term_name' => $term_name,
			'description' => $description
		]);
	}

	public function deleteOntology(int $id)
	{
		$this->builder->where('id', $id);
		$this->builder->delete();
	}

	public function getOntologyNameById(int $id): ?string
	{
		$this->builder->select('name');
		$this->builder->where('id', $id);

		$result =  $this->builder->get()->getResultArray();

		if (count($result) == 1){
			return $result[0]['name'];
		}

		return null;
	}
}
