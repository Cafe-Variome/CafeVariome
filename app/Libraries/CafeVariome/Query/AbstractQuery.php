<?php namespace App\Libraries\CafeVariome\Query;


use App\Models\Settings;
use Elasticsearch\ClientBuilder;

/**
 * AbstractQuery.php
 * Created 13/07/2021
 *
 * @author Mehdi Mehtarizadeh
 *
 */

abstract class AbstractQuery
{

	public abstract function execute(array $clause, int $source_id, bool $iscount);

	protected function getNeo4JInstance(): \GraphAware\Neo4j\Client\ClientInterface
	{
		$setting = Settings::getInstance();

		$neo4jUsername = $setting->getNeo4JUserName();
		$neo4jPassword = $setting->getNeo4JPassword();
		$baseNeo4jAddress = $setting->getNeo4JUri();
		$neo4jPort = $setting->getNeo4JPort();

		if (strpos($baseNeo4jAddress, 'http://') !== false) {
			$baseNeo4jAddress = str_replace("http://","",$baseNeo4jAddress);
		}
		if (strpos($baseNeo4jAddress, 'https://') !== false) {
			$baseNeo4jAddress = str_replace("https://","",$baseNeo4jAddress);
		}

		return \GraphAware\Neo4j\Client\ClientBuilder::create()
			->addConnection('default', 'http://' . $neo4jUsername . ':' . $neo4jPassword . '@' . $baseNeo4jAddress.':' . $neo4jPort)
			->setDefaultTimeout(60)
			->build();
	}

	protected function getESInstance(): \Elasticsearch\Client
	{
		$setting = Settings::getInstance();

		$hosts = array($setting->getElasticSearchUri());
		return ClientBuilder::create()->setHosts($hosts)->build();
	}
}
