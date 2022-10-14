<?php namespace App\Libraries\CafeVariome\Entities\ViewModels;

use App\Libraries\CafeVariome\Helpers\UI\TaskHelper;

/**
 * TaskList.php
 * Created 14/10/2022
 *
 * @author Mehdi Mehtarizadeh
 */

class TaskList extends BaseViewModel
{
	public string $type;

	public string $started;

	public ?string $ended;

	public string $progress;

	public ?string $error_code;

	public ?string $error_message;

	public int $user_id;

	public string $user_first_name;

	public string $user_last_name;

	public string $status;

	public function __construct(object $input = null)
	{
		if (!is_null($input))
		{
			parent::__construct($input);
			$this->type = TaskHelper::GetTaskType($this->type);
			$this->started = date("H:i:s d-m-Y T", $this->started);
			$this->ended = is_null($this->ended) ? '' : date("H:i:s d-m-Y T", $this->ended);
			$this->progress .= ' %';
			$this->error_code = TaskHelper::GetTaskError($this->error_code);
			$this->status = TaskHelper::GetTaskStatus($this->status);
		}
	}
}
