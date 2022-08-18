<?php namespace App\Libraries\CafeVariome\Entities\ViewModels;

/**
 * AttributeList.php
 * Created 18/08/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Helpers\UI\AttributeHelper;

class AttributeList extends BaseViewModel
{
	public string $name;

	public string $display_name;

	public int $type;

	public string $type_text;

	public string $storage_location;

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
