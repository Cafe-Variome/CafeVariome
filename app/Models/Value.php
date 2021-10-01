<?php namespace App\Models;

/**
 * Name Value.php
 * @author Mehdi Mehtarizadeh
 *
 * Model class for Value entities.
 */

use \CodeIgniter\Model;

class Value extends Model
{
	protected $db;
	protected $table      = 'values';
	protected $builder;

	protected $primaryKey = 'id';

	public function __construct()
	{
		$this->db = \Config\Database::connect();
		$this->builder = $this->db->table($this->table);
	}

	public function createValue(string $name, int $attribute_id, string $display_name = '', int $frequency = 0,
								bool $show_in_interface = true, bool $include_in_interface_index = true): int
	{
		$this->builder->insert([
			'name' => $name,
			'attribute_id' => $attribute_id,
			'display_name' => $display_name,
			'frequency' => $frequency,
			'show_in_interface' => $show_in_interface,
			'include_in_interface_index' => $include_in_interface_index
		]);
		return $this->db->insertID();
	}

	public function getValue(int $value_id)
	{
		$this->builder->select();
		$this->builder->where('id', $value_id);
		$result = $this->builder->get()->getResultArray();

		if (count($result) == 1) {
			return $result[0];
		}

		return null;
	}

	public function updateValue(int $value_id, string $display_name, bool $show_in_interface, bool $include_in_interface_index)
	{
		$this->builder->where('id', $value_id);
		$this->builder->update([
			'display_name' => $display_name,
			'show_in_interface' => $show_in_interface,
			'include_in_interface_index' => $include_in_interface_index
		]);
	}

	public function getValueIdByNameAndAttributeId(string $name, int $attribute_id): int
	{
		$value_id = -1;

		$this->builder->where(['name' => $name, 'attribute_id' => $attribute_id]);
		$result = $this->builder->get()->getResultArray();

		if (count($result) == 1) {
			$value_id = $result[0]['id'];
		}

		return $value_id;
	}

	public function updateFrequency(int $id, float $frequency, bool $add = true):bool
	{
		if ($add) {
			$currentFrequency = $this->getFrequency($id);
			if ($currentFrequency > 0) {
				$frequency = $frequency + $currentFrequency;
			}
		}
		$this->builder->where('id', $id);

		return $this->builder->update(['frequency' => $frequency]);
	}

	public function getFrequency(int $id): float
	{
		$this->builder->select('frequency');
		$this->builder->where('id', $id);
		$result = $this->builder->get()->getResultArray();

		if(count($result) == 1){
			return $result[0]['frequency'];
		}

		return -1;
	}

	public function deleteAbsentValue(int $id)
	{
		$this->builder->where('id', $id);
		$this->builder->where('frequency', 0); // Frequency = 0 indicates a value is absent and is no more needed.
		$this->builder->delete();
	}

	public function getValuesByAttributeId(int $attribute_id): array
	{
		$this->builder->select('id, name, display_name, frequency');
		$this->builder->where('attribute_id', $attribute_id);

		return $this->builder->get()->getResultArray();
	}

	public function countValuesByAttributeId(int $attribute_id): int
	{
		$this->builder->select('id, name, display_name, frequency');
		$this->builder->where('attribute_id', $attribute_id);

		return $this->builder->countAll();
	}
}
