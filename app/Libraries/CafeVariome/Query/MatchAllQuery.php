<?php namespace App\Libraries\CafeVariome\Query;

use App\Models\EAV;
use App\Models\Elastic;
use App\Models\Settings;
use App\Models\Source;
use Elasticsearch\ClientBuilder;

/**
 * MatchAllQuery.php
 * Created 13/06/2021
 *
 * @author Mehdi Mehtarizadeh
 *
 */

class MatchAllQuery extends AbstractQuery
{
	private $aggregate_size;

	public function __construct()
	{
		$this->aggregate_size = ELASTICSERACH_AGGREGATE_SIZE;
	}

	public function execute(array $clause, int $source_id, bool $iscount)
	{
		$elasticModel = new Elastic();
		$es_client = $this->getESInstance();

		$es_index = $elasticModel->getTitlePrefix() . "_" . $source_id;
		$esQuery = ['index' => $es_index];
		$esQuery['body']['query']['exists']['field'] = 'subject_id';
		$esQuery['body']['aggs']['punique']['terms']=['field'=>'subject_id','size' => $this->aggregate_size];

		$results = $es_client->search($esQuery);

		if ($iscount){
			$result = $results['hits']['total'] > 0 && count($results['aggregations']['punique']['buckets']) > 0 ? count($results['aggregations']['punique']['buckets']) : 0;
		}
		else{
			$result = array_column($results['aggregations']['punique']['buckets'], 'key');
		}

		return $result;
	}
}
