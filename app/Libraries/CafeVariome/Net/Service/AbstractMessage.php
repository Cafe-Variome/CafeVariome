<?php namespace App\Libraries\CafeVariome\Net\Service;

/**
 * AbstractMessage.php
 * Created 18/07/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\CafeVariome;
use App\Libraries\CafeVariome\Database\SettingAdapter;

abstract class AbstractMessage implements IMessage
{
	protected string $installation_key;

	public function __construct()
	{
		return $this;
	}
	public function ToJson(): string
	{
		return json_encode(get_object_vars($this));
	}

	public function SetInstallationKey(string $installation_key): static
	{
		$this->installation_key = $installation_key;
		return $this;
	}

	protected function SetType(bool $full = false): void
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
