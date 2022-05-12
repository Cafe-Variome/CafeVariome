<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * CredentialFactory.php
 * Created 03/05/2022
 *
 * This class handles object creation of the Credential class.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\Credential;
use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\NullEntity;

class CredentialFactory extends EntityFactory
{
	/**
	 * @param object|null $input
	 * @return IEntity
	 * @throws \Exception
	 */
	public function getInstance(?object $input): IEntity
	{
		if (is_null($input) || count($objectVars = get_object_vars($input)) == 0 )
		{
			return new NullEntity();
		}

		$properties = [];
		foreach ($objectVars as $var => $value)
		{
			$properties[$var] = $value;
		}

		return new Credential($properties);
	}

	/**
	 * @param string $name
	 * @param string $username
	 * @param string $password
	 * @param bool $hide_username
	 * @param bool $removable
	 * @return Credential
	 * @throws \Exception
	 */
	public function getInstanceFromParameters(string $name, ?string $username, ?string $password, bool $hide_username, string $hash = '', bool $encrypt = true, bool $removable = true): Credential
	{
		return new Credential([
			'name' => $name,
			'username' => $username,
			'password' => $password,
			'hash' => $hash,
			'hide_username' => $hide_username,
			'removable' => $removable
		], $encrypt);
	}
}
