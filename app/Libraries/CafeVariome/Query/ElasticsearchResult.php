<?php namespace App\Libraries\CafeVariome\Query;

use App\Models\Elastic;
use App\Models\Settings;
use App\Models\Source;
use Elasticsearch\ClientBuilder;

/**
 * ElasticsearchResult.php
 * Created 23/07/2021
 *
 * @author Mehdi Mehtarizadeh
 *
 */


class ElasticsearchResult extends AbstractResult
{
	private $aggregate_size;

	public function __construct()
	{
		$this->aggregate_size = ELASTICSERACH_EXTRACT_AGGREGATE_SIZE;
	}

    public function extract(array $ids, string $attribute, int $source_id): array
    {
		$elasticClient = $this->getESInstance();
		$es_index = $this->getESIndexName($source_id);

		$paramsnew = ['index' => $es_index];

		$paramsnew['size'] = $this->aggregate_size;
		$paramsnew['body']['query']['bool']['must'][0]['term']['source_id'] = $source_id;
		$paramsnew['body']['query']['bool']['must'][1]['term']['type'] = "eav";
		$paramsnew['body']['query']['bool']['must'][2]['term']['attribute'] = $attribute;
		foreach ($ids as $id) {
			$paramsnew['body']['query']['bool']['should'][] = ['term' => ['subject_id' => $id]];
		}

		$paramsnew['body']['query']['bool']["minimum_should_match"] = 1;

		$results = $elasticClient->search($paramsnew);

		$final = [];
		foreach ($results['hits']['hits'] as $hit) {
			$id =  $hit['_source']['subject_id'];
			$val =  $hit['_source']['value'];
			if (!array_key_exists($id, $final)){
				$final[$id] = [$val];
			}
			else{
				array_push($final[$id], $val);
			}
		}

		return $final;
    }

	protected function getESInstance(): \Elasticsearch\Client
	{
		$setting = Settings::getInstance();

		$hosts = array($setting->getElasticSearchUri());
		return ClientBuilder::create()->setHosts($hosts)->build();
	}
}
