<?php namespace App\Libraries\CafeVariome\Entities;

/**
 * Value.php
 * Created 28/07/2022
 *
 * This class extends Entity.
 * @author Mehdi Mehtarizadeh
 */

class Value extends Entity
{
	public string $name;

	public int $attribute_id;

	public string $display_name;

	public int $frequency;

	public bool $show_in_interface;

	public bool $include_in_interface_index;

	public function __construct(array $properties)
	{
		parent::__construct($properties);
	}
}
