<?php namespace App\Libraries\CafeVariome\Net\Service;

/**
 * PollProgressMessage.php
 * Created 25/07/2022
 *
 * @author Mehdi Mehtarizadeh
 */

class PollProgressMessage extends AbstractMessage
{
	protected string $type;

	public function __construct()
	{
		$this->SetType();
	}
}
