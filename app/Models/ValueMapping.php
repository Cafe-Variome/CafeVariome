<?php namespace App\Models;

/**
 * Name ValueMapping.php
 * @author Mehdi Mehtarizadeh
 *
 * Model class for ValueMapping entities.
 */

use \CodeIgniter\Model;

class ValueMapping extends Model
{
	protected $db;
	protected $table      = 'value_mappings';
	protected $builder;

	protected $primaryKey = 'id';

	public function __construct()
	{
		$this->db = \Config\Database::connect();
		$this->builder = $this->db->table($this->table);
	}

	public function createValueMapping(string $name, int $value_id): int
	{
		$this->builder->insert([
			'name' => $name,
			'value_id' => $value_id
		]);

		return $this->db->insertID();
	}

	public function getValueMapping(int $id)
	{
		$this->builder->where('id', $id);
		$result = $this->builder->get()->getResultArray();

		return count($result) == 1 ? $result[0] : null;
	}

	public function getValueMappingsByValueId(int $value_id): array
	{
		$this->builder->where('value_id', $value_id);
		return $this->builder->get()->getResultArray();
	}

	public function deleteValueMapping(int $id)
	{
		$this->builder->where('id',  $id);
		$this->builder->delete();
	}
}
