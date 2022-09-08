<?php namespace App\Libraries\CafeVariome\Query;

/**
 * AbstractResult.php
 * Created 23/07/2021
 *
 * @author Mehdi Mehtarizadeh
 *
 */

use App\Libraries\CafeVariome\Core\DataPipeLine\Index\Neo4J;
use App\Libraries\CafeVariome\Entities\Source;
use App\Libraries\CafeVariome\Helpers\Core\ElasticsearchHelper;

abstract class AbstractResult
{
	public abstract function Extract(array $ids, string $attribute, Source $source): array;

	protected function getESIndexName(): string
	{
		return ElasticsearchHelper::GetESIndexPrefix();
	}

	protected function getNeo4JInstance(): Neo4J
	{
		return new Neo4J();
	}
}
