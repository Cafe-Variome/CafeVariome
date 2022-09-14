<?php namespace App\Libraries\CafeVariome\Database;

/**
 * BaseAdapter.php
 * Created 22/04/2022
 *
 * This abstract class offers a template for CRUD and other database operations.
 * @author Mehdi Mehtarizadeh
 *
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use \Config\Database;

abstract class BaseAdapter implements IAdapter
{
	/**
	 * @var \CodeIgniter\Database\BaseConnection
	 */
	protected $db;

	/**
	 * @var string name of the corresponding table in the database
	 */
	protected static string $table;

	/**
	 * @var string primary key of the corresponding table in the database
	 */
	protected static string $key;

	/**
	 * @var array list of properties that are foreign keys of other entities
	 */
	protected array $foreign_keys;

	/**
	 * @var \CodeIgniter\Database\BaseBuilder
	 */
	protected $builder;

	protected ?string $binding;

	protected string $entity_class;

	protected array $related_entities;

	/**
	 * constructor
	 */
	public function __construct()
	{
		$this->db = Database::connect();
		$this->builder = $this->db->table(static::$table);
		$this->binding = null;
		$className = explode('\\', get_class($this))[count(explode('\\', get_class($this))) - 1];
		$this->entity_class = '\\' . CAFEVARIOME_NAMESPACE . '\\' . 'Entities' . '\\' . str_replace('Adapter', '', $className);
		$this->related_entities = $this->GetRelatedEntities();
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
		$this->CompileSelect();
		$this->CompileJoin();
		$this->builder->where(static::$table . '.' . static::$key, $id);
		$results = $this->builder->get()->getResult();

		$record = null;
		if (count($results) == 1)
		{
			$record = $results[0];
		}

		return $this->binding != null ? $this->BindTo($record) : $this->toEntity($record);
	}

	public function ReadByIds(array $ids): array
	{
		if (count($ids) == 0)
		{
			return [];
		}

		$this->CompileSelect();
		$this->CompileJoin();
		$this->builder->whereIn(static::$table . '.' . static::$key, $ids);
		$results = $this->builder->get()->getResult();

		$entities = [];
		for($c = 0; $c < count($results); $c++)
		{
			$entities[$results[$c]->{static::$key}] = $this->binding != null ? $this->BindTo($results[$c]) : $this->toEntity($results[$c]);
		}

		return $entities;
	}

	/**
	 * @return array
	 */
	public function ReadAll(): array
	{
		$this->CompileSelect();
		$this->CompileJoin();
		$results = $this->builder->get()->getResult();

		$entities = [];
		for($c = 0; $c < count($results); $c++)
		{
			$entities[$results[$c]->{static::$key}] = $this->binding != null ? $this->BindTo($results[$c]) : $this->toEntity($results[$c]);
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
		$this->builder->where(static::$table . '.' . static::$key, $id);
		return $this->builder->update($object->toArray());
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function Delete(int $id): bool
	{
		$this->builder->where(static::$table . '.' . static::$key, $id);
		return $this->builder->delete();
	}


	/**
	 * @param object|null $object
	 * @return IEntity
	 */
	public abstract function toEntity(?object $object): IEntity;

	/**
	 * @return string name of the database table
	 */
	public static function GetTable(): string
	{
		return static::$table;
	}

	/**
	 * @return string name of the primary key column of the database table
	 */
	public static function GetKey(): string
	{
		return static::$key;
	}

	protected function changeTable(string $table)
	{
		$this->builder = $this->db->table($table);
	}

	protected function resetTable()
	{
		$this->builder = $this->db->table(static::$table);
	}

	public function SetModel(string $class_to_bind)
	{
		$this->binding = $class_to_bind;
		return $this;
	}

	protected function BindTo(object $data): IEntity
	{
		return new $this->binding($data);
	}

	protected function GetRelatedEntities(): array
	{
		$relatedEntities = [];

		$properties = is_null($this->binding) ? $this->entity_class::GetProperties() : $this->binding::GetProperties();

		foreach ($properties as $property)
		{
			$reflectionProperty = new \ReflectionProperty($this->binding ?? $this->entity_class, $property);
			$reflectionType = $reflectionProperty->getType();
			if (str_ends_with(strtolower($property), '_id') || $reflectionType->getName() == 'array')
			{
				$propertyName = str_replace('_id', '', $property);
				$entityName = $this->GetEntityName($propertyName);
				$adapterClass = CAFEVARIOME_NAMESPACE . '\\Database\\' . $entityName . 'Adapter';

				$relatedEntities[$propertyName] = [
					'entity' => $entityName,
					'table' => $adapterClass::GetTable(),
					'primary_key' => $adapterClass::GetKey(),
					'type' => $reflectionType->getName(),
					'nullable' => $reflectionType->allowsNull()
				];
			}
		}

		return $relatedEntities;
	}

	protected function ToPascalCase(string $str): string
	{
		$fc = strtoupper($str[0]);
		return $fc . substr($str, 1);
	}

	protected function IsPrimitive(string $type): bool
	{
		return in_array(
			$type,
			['string', 'int', 'integer', 'bool', 'boolean', 'double', 'float']
		);
	}

	protected function GetEntityName(string $singular_name): string
	{
		if (str_contains($singular_name, '_'))
		{
			$name = '';
			$nameArray = explode('_', $singular_name);
			for($c = 0; $c < count($nameArray); $c++)
			{
				$name .= $this->ToPascalCase($nameArray[$c]);
			}
			return $name;
		}
		else
		{
			return $this->ToPascalCase($singular_name);
		}
	}

	protected function CompileSelect()
	{
		$entityProperties = $this->entity_class::GetProperties();
		$hasBinding = !is_null($this->binding);

		if ($hasBinding)
		{
			$properties = $this->binding::GetProperties();
		}
		else
		{
			$properties = $entityProperties;
		}

		$selectStatement = '';

		foreach ($properties as $property)
		{
			$reflectionProperty = new \ReflectionProperty($this->binding ?? $this->entity_class, $property);

			if (
				str_contains($property, '_') &&
				array_key_exists($rEntity = explode('_', $property)[0], $this->related_entities) &&
				!str_ends_with($property, '_id')
			)
			{
				$selectStatement .= $this->related_entities[$rEntity]['table'] . '.' . str_replace($rEntity . '_', '', $property) . ' as ' . $property . ', ';
			}
			else if (
				$reflectionProperty->hasType() &&
				$this->IsPrimitive($reflectionProperty->getType()->getName()) &&
				in_array($property, $entityProperties)
			)
			{
				$selectStatement .= static::$table . '.' . $property . ', ';
			}

		}

		$selectStatement = rtrim($selectStatement, ',');
		$this->builder->select($selectStatement);
	}

	protected function CompileJoin()
	{
		foreach ($this->related_entities as $entity => $details)
		{
			$this->builder->join(
				$details['table'],
				static::$table . '.' . $entity .'_id = ' . $details['table'] . '.' . $details['primary_key'],
				$details['nullable'] ? 'LEFT' : 'INNER'
			);
		}
	}
}
