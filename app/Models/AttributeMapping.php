<?php namespace App\Models;

/**
 * Name AttributeMapping.php
 * @author Mehdi Mehtarizadeh
 * @deprecated
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

	public function getAttributeByMappingNameAndSourceId(string $name, int $source_id)
	{
		$this->builder->select('attributes.id as attribute_id, attributes.name as attribute_name');
		$this->builder->where($this->table . '.name', $name);
		$this->builder->join('attributes', $this->table . '.attribute_id = attributes.id');
		$this->builder->where('attributes.source_id', $source_id);
		$result = $this->builder->get()->getResultArray();

		if (count($result) == 1){
			return [
				'id' => $result[0]['attribute_id'],
				'name' => $result[0]['attribute_name'],
			];
		}

		return null;
	}

	public function getAttributeMappingsByAttributeId(int $attribute_id): array
	{
		$this->builder->where('attribute_id', $attribute_id);
		return $this->builder->get()->getResultArray();
	}

	public function attributeMappingExists(string $name, int $source_id, int $id = -1)
	{
		$this->builder->select($this->table . '.name');
		$this->builder->where($this->table . '.name', $name);
		if($id > 0){
			$this->builder->where($this->table . '.id!=', $id);
		}
		$this->builder->join('attributes', $this->table . '.attribute_id = attributes.id');
		$this->builder->where('attributes.source_id', $source_id);
		$result = $this->builder->get()->getResultArray();

		return count($result) > 0;
	}

	public function deleteAttributeMapping(int $id)
	{
		$this->builder->where('id',  $id);
		$this->builder->delete();
	}
}
