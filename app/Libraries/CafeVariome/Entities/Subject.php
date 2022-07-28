<?php namespace App\Libraries\CafeVariome\Entities;

/**
 * Subject.php
 * Created 27/07/2022
 *
 * This class extends Entity and implements IEntity.
 * @author Mehdi Mehtarizadeh
 */

class Subject extends Entity
{
	public string $name;

	public int $source_id;

	public function __construct(array $properties)
    {
        parent::__construct($properties);
    }
}
