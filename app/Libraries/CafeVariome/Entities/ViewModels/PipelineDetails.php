<?php namespace App\Libraries\CafeVariome\Entities\ViewModels;

/**
 * PipelineDetails.php
 * Created 10/08/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Helpers\UI\PipelineHelper;

class PipelineDetails extends BaseViewModel
{
	public string $name;

	public string $subject_id_location;

	public string $subject_id_attribute_name;

	public int $subject_id_assignment_batch_size;

	public string $subject_id_prefix;

	public string $expansion_columns;

	public ?string $expansion_policy;

	public ?string $expansion_attribute_name;

	public string $grouping;

	public ?string $group_columns;

	public ?int $dateformat;

	public ?string $internal_delimiter;

	public function __construct(object $input = null)
	{
		if (!is_null($input))
		{
			parent::__construct($input);
			$this->subject_id_location = PipelineHelper::GetSubjectIDLocation($this->subject_id_location);
			$this->grouping = PipelineHelper::GetGrouping($this->grouping);
			$this->expansion_policy = $this->expansion_policy != null ? PipelineHelper::GetExpansionPolicy($this->expansion_policy) : 'N/A';
		}
	}
}
