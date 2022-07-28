<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * UserFactory.php
 * Created 27/04/2022
 *
 * This class handles object creation of the User class.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\User;

class UserFactory extends EntityFactory
{
	/**
	 * @param object|null $input
	 * @return IEntity
	 * @throws \Exception
	 */
	public function GetInstance(?object $input): IEntity
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

		return new User($properties);
	}

	public function getInstanceFromParameters(
		string $email,
		string $username,
		string $first_name,
		string $last_name,
		string $ip_address,
		?int $created_on,
		?string $phone = null,
		?string $company = null,
		bool $is_admin = false,
		bool $remote = false,
		bool $active = true
	): User
	{
		return new User(
			[
				'email' => $email,
				'username' => $username,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'phone' => $phone,
				'company' => $company,
				'ip_address' => $ip_address,
				'created_on' => $created_on ?? time(),
				'is_admin' => $is_admin,
				'remote' => $remote,
				'active' => $active,
			]
		);
	}
}
