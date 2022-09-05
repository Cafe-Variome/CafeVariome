<?php namespace App\Libraries\CafeVariome\Query;


use App\Libraries\CafeVariome\Core\DataPipeLine\Index\Neo4J;
use App\Libraries\CafeVariome\Helpers\Core\ElasticsearchHelper;

/**
 * AbstractResult.php
 * Created 23/07/2021
 *
 * @author Mehdi Mehtarizadeh
 *
 */


abstract class AbstractResult
{
	public abstract function extract(array $ids, string $attribute, int $source_id): array;

	protected function getESIndexName(int $source_id): string
	{
		return ElasticsearchHelper::GetSourceIndexName($source_id);
	}

	protected function getNeo4JInstance(): Neo4J
	{
		return new Neo4J();
	}
}
