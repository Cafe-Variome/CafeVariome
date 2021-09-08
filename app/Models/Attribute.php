<?php namespace App\Models;

/**
 * Name Attribute.php
 * @author Mehdi Mehtarizadeh
 *
 * Model class for Attribute entities.
 */

use CodeIgniter\Database\ConnectionInterface;
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

	public function createAttribute(string $name, int $source_id, string $display_name = '', int $type = 0,
									float $min = 0.0, float $max = 0.0, bool $show_in_interface = true,
									bool $include_in_interface_index = false, int $storage_location = ATRRIBUTE_STORAGE_UNDEFINED): int
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

	public function getAttributes(string $cols = null, array $conds = null, array $groupby = null, bool $isDistinct = false, int $limit = -1, int $offset = -1)
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
}
