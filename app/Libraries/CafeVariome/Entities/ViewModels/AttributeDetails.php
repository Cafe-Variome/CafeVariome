<?php namespace App\Libraries\CafeVariome\Entities\ViewModels;

/**
 * AttributeDetails.php
 * Created 18/08/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Helpers\UI\AttributeHelper;

class AttributeDetails extends BaseViewModel
{
	public string $name;

	public int $source_id;

	public string $display_name;

	public float $min;

	public float $max;

	public int $type;

	public string $type_text;

	public string $storage_location;

	public bool $show_in_interface;

	public bool $include_in_interface_index;

	public string $source_name;

	public function __construct(object $input = null)
	{
		if (!is_null($input))
		{
			parent::__construct($input);
			$this->type_text = AttributeHelper::GetAttributeType($input->type);
			$this->storage_location = AttributeHelper::GetAttributeStorageLocation($input->storage_location);
		}
	}
}
