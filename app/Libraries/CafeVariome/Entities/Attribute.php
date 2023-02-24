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
	/**
	 * @var string name of attribute
	 */
	public string $name;

	/**
	 * @var string display name of attribute used in query interface
	 */
	public string $display_name;

	/**
	 * @var int source ID of the attribute
	 */
	public int $source_id;

	/**
	 * @var int type of attribute
	 * @see app/Config/Constants.php for types
	 */
	public int $type;

	/**
	 * @var float minimum value of attribute if applicable
	 */
	public float $min;

	/**
	 * @var float maximum value of attribute if applicable
	 */
	public float $max;

	/**
	 * @var bool whether the attribute is shown in the query interface, not used
	 */
	public bool $show_in_interface;

	/**
	 * @var bool whether the attribute is shown in the query interface index
	 */
	public bool $include_in_interface_index;

	/**
	 * @var int where the attribute is stored
	 */
	public int $storage_location;
}
