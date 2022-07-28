<?php namespace App\Libraries\CafeVariome\Entities;

/**
 * EAV.php
 * Created 26/07/2022
 *
 * This class extends Entity and implements IEntity.
 * @author Mehdi Mehtarizadeh
 */

class EAV extends Entity
{
	public int $attribute_id;

	public int $value_id;

	public string $subject_id;

	public int $group_id;

	public int $file_id;

	public bool $indexed;

	public function __construct(array $properties)
	{
		parent::__construct($properties);
	}
}
