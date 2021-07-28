<?php namespace App\Libraries\CafeVariome\Query;


use App\Libraries\CafeVariome\Core\DataPipeLine\Stream\Neo4J;
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

	protected function getNeo4JInstance(): Neo4J
	{
		$neo4j = new Neo4J();
		return $neo4j;
	}

	protected function getESInstance(): \Elasticsearch\Client
	{
		$setting = Settings::getInstance();

		$hosts = array($setting->getElasticSearchUri());
		return ClientBuilder::create()->setHosts($hosts)->build();
	}
}
