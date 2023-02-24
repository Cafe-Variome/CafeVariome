<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * NetworkRequestFactory.php
 * Created 30/01/2023
 *
 * This class handles object creation of the NetworkRequest class.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\NetworkRequest;
use App\Libraries\CafeVariome\Entities\NullEntity;


class NetworkRequestFactory extends EntityFactory
{
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

		return new NetworkRequest($properties);
	}

	public function GetInstanceFromParameters(int $network_key, string $installation_key, string $url, string $justification, string $email, string $ip, string $token, int $status): NetworkRequest
	{
		return new NetworkRequest([
			'network_key' => $network_key,
			'installation_key' => $installation_key,
			'url' => $url,
			'justification' => $justification,
			'email' => $email,
			'ip' => $ip,
			'token' => $token,
			'status' => $status
		]);
	}
}
