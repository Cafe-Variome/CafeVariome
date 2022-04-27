<?php namespace App\Libraries\CafeVariome\Database;

/**
 * IAdapter.php
 * Created 22/04/2022
 *
 * This interface defines basic and shared functions of Adapter classes of Entities that are kept in the database.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;

interface IAdapter
{
	/**
	 * Creates a record of an object that implements IEntity in the corresponding database table.
	 *
	 * @param IEntity $object
	 * @return int primary key of the created record
	 */
	public function Create(IEntity $object): int;

	/**
	 * Reads a record from the corresponding database table and returns an object that implements IEntity.
	 *
	 * @param int $id primary key of the record
	 * @return IEntity
	 */
	public function Read(int $id): IEntity;

	/**
	 * Reads all records from the corresponding database table and returns an array of objects that implement IEntity.
	 *
	 * @return array
	 */
	public function ReadAll(): array;

	/**
	 * Updates a record in the corresponding database table.
	 *
	 * @param int $id primary key of the record
	 * @param IEntity $object encapsulated object that implements IEntity with new property values.
	 * @return bool
	 */
	public function Update(int $id, IEntity $object): bool;

	/**
	 * Deletes a record in the corresponding database table.
	 *
	 * @param int $id primary key of the record
	 * @return bool
	 */
	public function Delete(int $id): bool;

	/**
	 * Coverts a general PHP object to an object that implements IEntity
	 *
	 * @param object $object
	 * @return IEntity
	 */
	public function toEntity(?object $object): IEntity;
}
