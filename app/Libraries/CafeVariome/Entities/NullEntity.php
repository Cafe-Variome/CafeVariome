<?php namespace App\Libraries\CafeVariome\Entities;

/**
 * NullEntity.php
 * Created 22/04/2022
 *
 * This class implements IEntity interface and represents a null entity.
 * @author Mehdi Mehtarizadeh
 */

class NullEntity implements IEntity
{
	/**
	 * Returns an empty array.
	 *
	 * @return array
	 */
    public function toArray(): array
    {
        return [];
    }

	/**
	 * Returns -1 that cannot be linked to any record in the database.
	 *
	 * @return int
	 */
	public function getID(): int
	{
		return -1;
	}

	/**
	 * Returns true as objects of this class represent null entities.
	 * @return bool
	 */
	public function isNull(): bool
	{
		return true;
	}
}
