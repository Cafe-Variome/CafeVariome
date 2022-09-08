<?php namespace App\Libraries\CafeVariome\Query;

/**
 * MatchAllQuery.php
 * Created 13/06/2021
 *
 * @author Mehdi Mehtarizadeh
 *
 */

use App\Libraries\CafeVariome\Entities\Source;

class MatchAllQuery extends AbstractQuery
{
	private $aggregate_size;

	public function __construct()
	{
		$this->aggregate_size = ELASTICSERACH_AGGREGATE_SIZE;
	}

	public function Execute(array $clause, Source $source)
	{
		$es_client = $this->getESInstance();
		$es_index = $source->GetElasticSearchIndexName($this->GetESIndexPrefix());
		$esQuery = ['index' => $es_index];
		$esQuery['body']['query']['exists']['field'] = 'subject_id';
		$esQuery['body']['aggs']['punique']['terms'] = ['field' => 'subject_id', 'size' => $this->aggregate_size];

		$results = $es_client->search($esQuery);
		$result = array_column($results['aggregations']['punique']['buckets'], 'key');

		return $result;
	}
}
