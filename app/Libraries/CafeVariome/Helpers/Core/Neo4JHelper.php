<?php namespace App\Libraries\CafeVariome\Helpers\Core;

use App\Libraries\CafeVariome\CafeVariome;
use Laudis\Neo4j\Authentication\Authenticate;
use Laudis\Neo4j\ClientBuilder;

/**
 * Neo4JHelper.php
 * Created 12/10/2021
 *
 * @author Mehdi Mehtarizadeh
 *
 *
 *
 */

class Neo4JHelper
{
	/**
	 * ping()
	 *
	 * pings Neo4J server and returns true if it responds, false otherwise.
	 *
	 * @return bool
	 */
	public static function ping():bool
	{
		$setting = CafeVariome::Settings();

		$neo4jUsername = $setting->GetNeo4JUserName();
		$neo4jPassword = $setting->GetNeo4JPassword();
		$neo4jAddress = $setting->GetNeo4JUri();
		$neo4jPort = $setting->GetNeo4JPort();

		$protocol = 'http';
		if (strpos($neo4jAddress, 'https://') !== false) {
			$protocol = 'https';
		}

		try {
			$neo4jClient = ClientBuilder::create()
				->withDriver($protocol, $neo4jAddress . ':' . $neo4jPort, Authenticate::basic($neo4jUsername, $neo4jPassword))
				->withDefaultDriver($protocol)
				->build();
			$result = $neo4jClient->run('MATCH (n:Person) RETURN n');
			return true;
		} catch (\Exception $ex) {

		}
		return false;
	}
}
