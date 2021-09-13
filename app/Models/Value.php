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
								bool $show_in_interface = true, bool $include_in_interface_index = false): int
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

	public function getValueIdByNameAndAttributeId(string $name, int $attribute_id): int
	{
		$value_id = -1;

		$this->builder->where(['name' => $name, 'attribute_id' => $attribute_id]);
		$result = $this->builder->get()->getResultArray();

		if (count($result) == 1) {
			$value_id = $result[0]['id'];;
		}

		return $value_id;
	}

	public function updateFrequencyByName(string $name, float $frequency, bool $add = true):bool
	{
		if ($add) {
			$currentFrequency = $this->getFrequencyByName($name);
			if ($currentFrequency > 0) {
				$frequency = $frequency + $currentFrequency;
			}
		}
		$this->builder->where('name', $name);
		return $this->builder->update(['frequency' => $frequency]);
	}

	public function getFrequencyByName(string $name): float
	{
		$this->builder->select('frequency');
		$this->builder->where('name', $name);
		$result = $this->builder->get()->getResultArray();

		if(count($result) == 1){
			return $result[0]['frequency'];
		}

		return -1;
	}

	public function getFrequencyById(int $id): float
	{
		$this->builder->select('frequency');
		$this->builder->where('id', $id);
		$result = $this->builder->get()->getResultArray();

		if(count($result) == 1){
			return $result[0]['frequency'];
		}

		return -1;
	}

	public function updateFrequencyById(int $id, float $frequency, bool $add = true):bool
	{
		if ($add) {
			$currentFrequency = $this->getFrequencyById($id);
			if ($currentFrequency > 0) {
				$frequency = $frequency + $currentFrequency;
			}
		}
		$this->builder->where('id', $id);
		return $this->builder->update(['frequency' => $frequency]);
	}

	public function deleteAbsentValueById(int $id)
	{
		$this->builder->where('id', $id);
		$this->builder->where('frequency', 0); // Frequency = 0 indicates a value is absent and is no more needed.
		$this->builder->delete();
	}
}
