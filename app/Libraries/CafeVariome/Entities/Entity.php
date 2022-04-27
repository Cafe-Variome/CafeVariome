<?php namespace App\Libraries\CafeVariome\Entities;

/**
 * Entity.php
 * Created 22/04/2022
 *
 * This interface implements IEntity and is a base class of all other Entity classes.
 * @author Mehdi Mehtarizadeh
 */

class Entity implements IEntity
{

	/**
	 * @var int ID of the entity as is assigned by the DBMS.
	 */
	protected int $id;

	/**
	 * constructor
	 *
	 * @param array $properties associative array of properties of the entity object to be created
	 * @throws \Exception if any array key in $properties does not exist in the definition of the entity class.
	 */
	public function __construct(array $properties)
	{
		$objectVars = get_class_vars(get_class($this));

		foreach ($properties as $property => $value)
		{
			if(array_key_exists($property, $objectVars))
			{
				$this->$property = $value;
			}
			else
			{
				throw new \Exception("Property $property does not exist in the definition of " . get_class($this) . '.');
			}
		}
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return get_object_vars($this);
	}

	/**
	 * Returns the ID value of the entity.
	 *
	 * @return int
	 */
	public function getID(): int
	{
		return $this->id;
	}

	/**
	 * If an object of this class or its children is created this guarantees that the object is not of NullEntity.
	 *
	 * @return bool
	 */
	public function isNull(): bool
	{
		return false;
	}
}
