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
}
