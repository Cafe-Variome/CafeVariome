<?php namespace App\Libraries\CafeVariome\Entities;

/**
 * Setting.php
 * Created 21/07/2022
 *
 * This class extends Entity and implements IEntity.
 * @author Mehdi Mehtarizadeh
 */

class Setting extends Entity
{
	public string $key;

	public string $value;

	public string $name;

	public string $info;

	public string $group;

	public function __construct(array $properties)
	{
		parent::__construct($properties);
	}
}
