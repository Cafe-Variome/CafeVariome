<?php namespace App\Libraries\CafeVariome\Database;

use App\Libraries\CafeVariome\Entities\IEntity;
use \Config\Database;

/**
 * BaseAdapter.php
 * Created 22/04/2022
 *
 * This abstract class offers a template for CRUD and other database operations.
 * @author Mehdi Mehtarizadeh
 *
 */

abstract class BaseAdapter implements IAdapter
{
	/**
	 * @var \CodeIgniter\Database\BaseConnection
	 */
	protected $db;

	/**
	 * @var string name of the corresponding table in the database
	 */
	protected string $table;

	/**
	 * @var string primary key of the corresponding table in the database
	 */
	protected string $key;

	/**
	 * @var array list of properties that are foreign keys of other entities
	 */
	protected array $foreign_keys;

	/**
	 * @var \CodeIgniter\Database\BaseBuilder
	 */
	protected $builder;

	/**
	 * constructor
	 */
	public function __construct()
	{
		$this->db = Database::connect();
		$this->builder = $this->db->table($this->table);
	}

	/**
	 * @param IEntity $object
	 * @return int
	 */
	public function Create(IEntity $object): int
	{
		$this->builder->insert($object->toArray());
		return $this->db->insertID();
	}

	/**
	 * @param int $id
	 * @return IEntity
	 */
	public function Read(int $id): IEntity
	{
		$this->builder->select();
		$this->builder->where($this->key, $id);
		$results = $this->builder->get()->getResult();

		$record = null;
		if (count($results) == 1)
		{
			$record = $results[0];
		}

		return $this->toEntity($record);
	}

	/**
	 * @return array
	 */
	public function ReadAll(): array
	{
		$this->builder->select();
		$results = $this->builder->get()->getResult();

		$entities = [];
		for($c = 0; $c < count($results); $c++)
		{
			$entities[$results[$c]->{$this->key}] = $this->toEntity($results[$c]);
		    //array_push($entities, $this->toEntity($results[$c]));
		}

		return $entities;
	}

	/**
	 * @param int $id
	 * @param IEntity $object
	 * @return bool
	 */
	public function Update(int $id, IEntity $object): bool
	{
		$this->builder->where($this->key, $id);
		return $this->builder->update($object->toArray());
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function Delete(int $id): bool
	{
		$this->builder->where($this->key, $id);
		return $this->builder->delete();
	}

	/**
	 * @return string name of the database table
	 */
	public function GetTable(): string
	{
		return $this->table;
	}

	/**
	 * @return string name of the primary key column of the database table
	 */
	public function GetKey(): string
	{
		return $this->key;
	}

	protected function changeTable(string $table)
	{
		$this->builder = $this->db->table($table);
	}

	protected function resetTable()
	{
		$this->builder = $this->db->table($this->table);
	}

	/**
	 * @param object|null $object
	 * @return IEntity
	 */
	public abstract function toEntity(?object $object): IEntity;

}
