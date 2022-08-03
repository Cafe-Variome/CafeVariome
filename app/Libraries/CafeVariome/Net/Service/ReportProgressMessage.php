<?php namespace App\Libraries\CafeVariome\Net\Service;

/**
 * ReportProgressMessage.php
 * Created 20/07/2022
 *
 * @author Mehdi Mehtarizadeh
 */

class ReportProgressMessage extends AbstractReportMessage
{
	public int $progress;

	public function __construct(int $task_id, int $progress, bool $finished = false, string $status = '')
	{
		parent::__construct($task_id, $finished, $status);
		$this->progress = $progress;
	}
}
