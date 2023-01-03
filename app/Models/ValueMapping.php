<?php namespace App\Models;

/**
 * Name ValueMapping.php
 * @author Mehdi Mehtarizadeh
 * @deprecated
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

	public function getValueByMappingNameAndAttributeId(string $name, int $attribute_id)
	{
		$this->builder->select('values.id as value_id, values.name as value_name');
		$this->builder->where($this->table . '.name', $name);
		$this->builder->join('values', $this->table . '.value_id = values.id');
		$this->builder->where('values.attribute_id', $attribute_id);
		$result = $this->builder->get()->getResultArray();

		if (count($result) == 1){
			return [
				'id' => $result[0]['value_id'],
				'name' => $result[0]['value_name'],
			];
		}

		return null;
	}

	public function getValueMappingsByValueId(int $value_id): array
	{
		$this->builder->where('value_id', $value_id);
		return $this->builder->get()->getResultArray();
	}

	public function valueMappingExists(string $name, int $attribute_id, int $id = 1): bool
	{
		$this->builder->select($this->table . '.name');
		$this->builder->where($this->table . '.name', $name);
		if($id > 0){
			$this->builder->where($this->table . '.id!=', $id);
		}
		$this->builder->join('values', $this->table . '.value_id = values.id');
		$this->builder->where('values.attribute_id', $attribute_id);
		$result = $this->builder->get()->getResultArray();

		return count($result) > 0;
	}

	public function deleteValueMapping(int $id)
	{
		$this->builder->where('id',  $id);
		$this->builder->delete();
	}
}
