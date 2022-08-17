<?php namespace App\Libraries\CafeVariome\Entities\ViewModels;

/**
 * BaseViewModel.php
 * Created 10/08/2022
 *
 * @author Mehdi Mehtarizadeh
 */

abstract class BaseViewModel implements \App\Libraries\CafeVariome\Entities\IEntity
{
	protected int $id;

	public function __construct(object $input = null)
	{
		if (!is_null($input))
		{
			foreach (get_object_vars($input) as $property => $val)
			{
				if (property_exists($this, $property))
				{
					$this->$property = $val;
				}
				else
				{
					throw new \Exception("Property $property does not exist in the definition of " . get_class($this) . '.');
				}
			}
		}
	}

	public static function GetProperties()
	{
		return array_keys(get_class_vars(get_called_class()));
	}
	public function toArray(): array
	{
		return get_object_vars($this);
	}

	public function getID(): int
	{
		return $this->id;
	}

	public function isNull(): bool
	{
		return false;
	}
}
