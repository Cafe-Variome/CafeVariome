<?php namespace App\Models;

/**
 * Name AttributeMapping.php
 * @author Mehdi Mehtarizadeh
 *
 * Model class for AttributeMapping entities.
 */

use \CodeIgniter\Model;

class AttributeMapping extends Model
{
	protected $db;
	protected $table      = 'attribute_mappings';
	protected $builder;

	protected $primaryKey = 'id';

	public function __construct()
	{
		$this->db = \Config\Database::connect();
		$this->builder = $this->db->table($this->table);
	}

	public function createAttributeMapping(string $name, int $attribute_id): int
	{
		$this->builder->insert([
			'name' => $name,
			'attribute_id' => $attribute_id
		]);

		return $this->db->insertID();
	}

	public function getAttributeMapping(int $id)
	{
		$this->builder->where('id', $id);
		$result = $this->builder->get()->getResultArray();

		return count($result) == 1 ? $result[0] : null;
	}

	public function getAttributeMappingsByAttributeId(int $attribute_id): array
	{
		$this->builder->where('attribute_id', $attribute_id);
		return $this->builder->get()->getResultArray();
	}

	public function deleteAttributeMapping(int $id)
	{
		$this->builder->where('id',  $id);
		$this->builder->delete();
	}



}
