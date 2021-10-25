<?php namespace App\Libraries\CafeVariome\Query;


use App\Libraries\CafeVariome\Core\DataPipeLine\Index\Neo4J;
use App\Libraries\CafeVariome\Helpers\Core\ElasticsearchHelper;
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
		return new Neo4J();
	}

	protected function getESInstance(): \Elasticsearch\Client
	{
		$setting = Settings::getInstance();

		$hosts = array($setting->getElasticSearchUri());
		return ClientBuilder::create()->setHosts($hosts)->build();
	}

	protected function getESIndexName(int $source_id): string
	{
		return ElasticsearchHelper::getSourceIndexName($source_id);
	}
}
