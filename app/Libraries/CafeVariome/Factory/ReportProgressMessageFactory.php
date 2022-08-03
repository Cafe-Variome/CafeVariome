<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * ReportProgressMessageFactory.php
 * Created 20/07/2022
 *
 * This is a factory class for handling object creation of ReportProgressMessageFactory class.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Net\Service\ReportProgressMessage;

class ReportProgressMessageFactory
{
	public function GetInstance(int $task_id, int $progress, bool $finished, string $status = ''): ReportProgressMessage
	{
		return new ReportProgressMessage($task_id, $progress, $finished, $status);
	}

}
