<?php namespace App\Models;

/**
 * Name Attribute.php
 * @author Mehdi Mehtarizadeh
 *
 * Model class for Attribute entities.
 */

use \CodeIgniter\Model;

class Attribute extends Model
{
	protected $db;
	protected $table      = 'attributes';
	protected $builder;

	protected $primaryKey = 'id';

	public function __construct()
	{
		$this->db = \Config\Database::connect();
		$this->builder = $this->db->table($this->table);

	}

	public function createAttribute(string $name, int $source_id, string $display_name = '', int $type = ATTRIBUTE_TYPE_UNDEFINED,
									float $min = 0.0, float $max = 0.0, bool $show_in_interface = true,
									bool $include_in_interface_index = true, int $storage_location = ATTRIBUTE_STORAGE_UNDEFINED): int
	{
		$this->builder->insert([
			'name' => $name,
			'display_name' => $display_name,
			'source_id' => $source_id,
			'type' => $type,
			'min' => $min,
			'max' => $max,
			'show_in_interface' => $show_in_interface,
			'include_in_interface_index' => $include_in_interface_index,
			'storage_location' => $storage_location
		]);

		return $this->db->insertID();
	}

	private function getAttributes(string $cols = null, array $conds = null, array $groupby = null, bool $isDistinct = false, int $limit = -1, int $offset = -1)
	{
		if ($cols) {
			$this->builder->select($cols);
		}
		if ($conds) {
			$this->builder->where($conds);
		}
		if ($groupby) {
			$this->builder->groupBy($groupby);
		}
		if ($isDistinct) {
			$this->builder->distinct();
		}
		if ($limit > 0) {
			if ($offset > 0) {
				$this->builder->limit($limit, $offset);
			}
			$this->builder->limit($limit);
		}

		return $this->builder->get()->getResultArray();
	}

	public function getAttributeById(int $attribute_id)
	{
		$this->builder->select();
		$this->builder->where('id', $attribute_id);
		$result = $this->builder->get()->getResultArray();

		if (count($result) == 1){
			return $result[0];
		}

		return null;
	}

	public function updateAttribute(int $attribute_id, string $display_name, bool $show_in_interface, bool $include_in_interface_index)
	{
		$this->builder->where('id', $attribute_id);
		$this->builder->update([
			'display_name' => $display_name,
			'show_in_interface' => $show_in_interface,
			'include_in_interface_index' => $include_in_interface_index
		]);
	}

	public function getAttributesBySourceId(int $source_id): array
	{
		$this->builder->where('source_id', $source_id);
		return $this->builder->get()->getResultArray();
	}

	public function getAllAttributes(): array
	{
		return $this->builder->get()->getResultArray();
	}

	public function getAttributeIdByNameAndSourceId(string $name, string $source_id): int
	{
		$attribute_id = -1;

		$this->builder->where(['name' => $name, 'source_id' => $source_id]);
		$result = $this->builder->get()->getResultArray();

		if (count($result) == 1){
			$attribute_id = $result[0]['id'];
		}

		return $attribute_id;
	}

	public function getAttributeNameById(int $attribute_id): ?string
	{
		$this->builder->select('name');
		$this->builder->where('id', $attribute_id);

		$result = $this->builder->get()->getResultArray();

		if (count($result) == 1){
			return $result[0]['name'];
		}

		return null;
	}

	public function getSourceIdByAttributeId(int $attribute_id): int
	{
		$this->builder->select('source_id');
		$this->builder->where('id', $attribute_id);
		$result = $this->builder->get()->getResultArray();

		if (count($result) == 1){
			return $result[0]['source_id'];
		}

		return -1;
	}

	public function getAttributeTypeByName(string $name): int
	{
		$this->builder->select('type');
		$this->builder->where('name', $name);

		$result = $this->builder->get()->getResultArray();

		if (count($result) == 1){
			return $result[0]['type'];
		}

		return -1;
	}

	public function setAttributeTypeByName(string $name, int $type)
	{
		$this->builder->where('name', $name);
		$this->builder->update(['type' => $type]);
	}

	public function getAttributeMinimumAndMaximumByName(string $name): array
	{
		$this->builder->select('min, max');
		$this->builder->where('name', $name);

		$result = $this->builder->get()->getResultArray();

		if (count($result) == 1){
			return [$result[0]['min'], $result[0]['max']];
		}

		return [];
	}

	public function setAttributeMinimumAndMaximumByName(string $name, float $minimum, float $maximum)
	{
		$this->builder->where('name', $name);
		$this->builder->update([
			'min' => $minimum,
			'max' => $maximum
		]);
	}
}
