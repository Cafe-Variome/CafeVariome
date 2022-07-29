<?php namespace App\Libraries\CafeVariome\Net\Service;

/**
 * AbstractMessage.php
 * Created 18/07/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\CafeVariome;

abstract class AbstractMessage implements IMessage
{
	protected string $installation_key;

	public function __construct()
	{
		$this->installation_key = CafeVariome::Settings()->GetInstallationKey();
	}

	public function ToJson(): string
	{
		return json_encode(get_object_vars($this));
	}

	protected function SetType(bool $full = false)
	{
		$className = get_class($this);

		if ($full)
		{
			$this->type = $className;
		}
		else
		{
			$classNameArray = explode('\\', get_class($this));
			$this->type = $classNameArray[count($classNameArray) - 1];
		}
	}

	public function GetType(): string
	{
		return $this->type;
	}
}
