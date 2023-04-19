<?php namespace App\Libraries\CafeVariome\Net\Service;

/**
 * AbstractReportMessage.php
 * Created 25/07/2022
 *
 * @author Mehdi Mehtarizadeh
 */

abstract class AbstractReportMessage extends AbstractMessage
{
	protected int $process_id;

	protected int $task_id;

	protected bool $finished;

	protected string $type;

	protected string $status;

	public function __construct(int $task_id, bool $finished = false, string $status = '')
	{
		$this->process_id = getmypid();
		$this->SetType();
		$this->task_id = $task_id;
		$this->finished = $finished;
		$this->status = $status;
	}
}
