<?php namespace App\Libraries\CafeVariome\Entities;

/**
 * IEntity.php
 * Created 22/04/2022
 *
 * This interface defines basic and shared functions of Entity classes the data of which is kept in the database.
 * @author Mehdi Mehtarizadeh
 */


interface IEntity
{
	/**
	 * Converts any object that implements IEntity to a PHP associative array.
	 *
	 * @return array
	 */
	public function toArray(): array;

	/**
	 * Returns the ID value of an object that implements IEntity.
	 *
	 * @return int
	 */
	public function getID(): int;

	/**
	 * Returns true if the object is of type NullEntity, false otherwise.
	 * @return bool
	 */
	public function isNull(): bool;
}
