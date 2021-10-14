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

	public function getAttribute(int $attribute_id)
	{
		$this->builder->select();
		$this->builder->where('id', $attribute_id);
		$result = $this->builder->get()->getResultArray();

		if (count($result) == 1){
			return $result[0];
		}

		return null;
	}

	public function getAttributeAndValues(int $attribute_id)
	{
		$this->builder->select($this->table . '.name as attribute_name,' . $this->table . '.type as attribute_type, values.id as value_id, values.name as value_name');
		$this->builder->where($this->table . '.id', $attribute_id);
		$this->builder->join('values', $this->table . '.id = values.attribute_id');

		$result = $this->builder->get()->getResultArray();

		if (count($result) > 0){
			$values = [];
			foreach ($result as $attribute_value){
				$values[$attribute_value['value_id']] = $attribute_value['value_name'];
			}

			return [
				'name' => $result[0]['attribute_name'],
				'type' => $result[0]['attribute_type'],
				'values' => $values
			];
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

	public function getAttributeIdsBySourceIdAndStorageLocation(int $source_id, int $storage_location)
	{
		$this->builder->select('id');
		$this->builder->where('source_id', $source_id);
		$this->builder->where('storage_location', $storage_location);
		$result = $this->builder->get()->getResultArray();

		$ids = [];
		foreach ($result as $id){
			array_push($ids, $id['id']);
		}

		return $ids;
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

	public function getAttributeType(int $id): int
	{
		$this->builder->select('type');
		$this->builder->where('id', $id);

		$result = $this->builder->get()->getResultArray();

		if (count($result) == 1){
			return $result[0]['type'];
		}

		return -1;
	}

	public function setAttributeType(int $id, int $type)
	{
		$this->builder->where('id', $id);
		$this->builder->update(['type' => $type]);
	}

	public function getAttributeMinimumAndMaximum(int $id): array
	{
		$this->builder->select('min, max');
		$this->builder->where('id', $id);

		$result = $this->builder->get()->getResultArray();

		if (count($result) == 1){
			return [$result[0]['min'], $result[0]['max']];
		}

		return [];
	}

	public function setAttributeMinimumAndMaximum(int $id, float $minimum, float $maximum)
	{
		$this->builder->where('id', $id);
		$this->builder->update([
			'min' => $minimum,
			'max' => $maximum
		]);
	}

	public function setAttributeStorageLocation(int $attribute_id, int $storage_location)
	{
		$this->builder->where('id', $attribute_id);
		$this->builder->update([
			'storage_location' => $storage_location
		]);
	}

	public function getOntologyPrefixIdsAndRelationshipIdsByAttributeId(int $attribute_id): array
	{
		$this->builder = $this->db->table('attributes_ontology_prefixes_relationships');
		$this->builder->select('prefix_id, relationship_id');
		$this->builder->where('attribute_id', $attribute_id);

		$result = $this->builder->get()->getResultArray();
		$this->builder = $this->db->table($this->table);

		return $result;
	}
	public function associateAttributeWithOntologyPrefixAndRelationship(int $attribute_id, int $prefix_id, int $relationship_id, int $ontology_id)
	{
		$this->builder = $this->db->table('attributes_ontology_prefixes_relationships');

		$this->builder->insert([
			'attribute_id' => $attribute_id,
			'prefix_id' => $prefix_id,
			'relationship_id' => $relationship_id,
			'ontology_id' => $ontology_id
		]);
		$this->builder = $this->db->table($this->table);
	}
}
