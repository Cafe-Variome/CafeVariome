<?php namespace App\Libraries\CafeVariome\Entities;

/**
 * Attribute.php
 * Created 28/07/2022
 *
 * This class extends Entity.
 * @author Mehdi Mehtarizadeh
 */

class Attribute extends Entity
{
	public string $name;

	public string $display_name;

	public int $source_id;

	public int $type;

	public float $min;

	public float $max;

	public bool $show_in_interface;

	public bool $include_in_interface_index;

	public int $storage_location;
}
