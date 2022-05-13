<?php namespace App\Libraries\CafeVariome\Factory;

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\ProxyServer;

class ProxyServerFactory extends EntityFactory
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

		return new ProxyServer($properties);
	}

	/**
	 * @param string $name
	 * @param int $port
	 * @param int $server_id
	 * @param int|null $credential_id
	 * @return ProxyServer
	 * @throws \Exception
	 */
	public function getInstanceFromParameters(string $name, int $port, int $server_id, ?int $credential_id): ProxyServer
	{
		return new ProxyServer([
			'name' => $name,
			'port' => $port,
			'server_id' => $server_id,
			'credential_id' => $credential_id
		]);
	}
}
