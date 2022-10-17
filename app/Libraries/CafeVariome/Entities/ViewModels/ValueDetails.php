<?php namespace App\Libraries\CafeVariome\Entities\ViewModels;

/**
 * ValueDetails.php
 * Created 17/10/2022
 *
 * @author Mehdi Mehtarizadeh
 */

class ValueDetails extends BaseViewModel
{
	public string $name;

	public int $attribute_id;

	public string $attribute_name;

	public string $display_name;

	public int $frequency;

	public bool $show_in_interface;

	public bool $include_in_interface_index;
}
