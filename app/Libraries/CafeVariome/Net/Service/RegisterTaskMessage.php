<?php namespace App\Libraries\CafeVariome\Net\Service;

/**
 * RegisterTaskMessage.php
 * Created 18/07/2022
 *
 * @author Mehdi Mehtarizadeh
 */

class RegisterTaskMessage extends AbstractReportMessage
{
	public function __construct(int $task_id, string $status = '')
	{
		parent::__construct($task_id, false, $status);
	}
}
