<?php namespace App\Libraries\CafeVariome\Entities\ViewModels;

/**
 * PipelineList.php
 * Created 10/08/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Helpers\UI\PipelineHelper;

class PipelineList extends BaseViewModel
{
	public string $name;

	public string $subject_id_location;

	public string $subject_id_attribute_name;

	public string $grouping;

	public function __construct(object $input = null)
	{
		if (!is_null($input))
		{
			parent::__construct($input);
			$this->subject_id_location = PipelineHelper::GetSubjectIDLocation($this->subject_id_location);
			$this->grouping = PipelineHelper::GetGrouping($this->grouping);
		}
	}

}
