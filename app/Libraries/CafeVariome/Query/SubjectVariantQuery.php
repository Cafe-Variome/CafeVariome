<?php namespace App\Libraries\CafeVariome\Query;

use App\Models\Elastic;
use App\Models\Source;

/**
 * SubjectVariantQuery.php
 * Created 14/07/2021
 *
 * @author Colin Veal
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 *
 */

class SubjectVariantQuery extends AbstractQuery
{

	public function __construct()
	{
		$this->aggregate_size = ELASTICSERACH_AGGREGATE_SIZE;
	}

	public function execute(array $clause, int $source_id, bool $iscount)
	{
		$es_client = $this->getESInstance();
		$es_index = $this->getESIndexName($source_id);

		$arr = [];
		foreach ($clause as $key => $value) { // replace with actual parameters
			$tmp = [];
			$tmp[]['match'] = ['attribute' => $key];
			$tmp[]['match'] = ['value.raw' => $value];
			$arr_child['has_child']['type'] = 'eav';

			$arr_child['has_child']['query']['bool']['must'] = $tmp;
			$arr[] = $arr_child;
		}

		$paramsnew = ['index' => $es_index, 'size' => 0];
		$paramsnew['body']['query']['bool']['must'][0]['term']['source_id'] = $source_id;
		$paramsnew['body']['query']['bool']['must'][1]['bool']['must'] = $arr;
		$paramsnew['body']['aggs']['punique']['terms'] = ['field' => 'subject_id', 'size' => $this->aggregate_size];

		$esquery = $es_client->search($paramsnew);

		if ($iscount)
		{
			$result = $esquery['hits']['total'] > 0 && count($esquery['aggregations']['punique']['buckets']) > 0 ? count($esquery['aggregations']['punique']['buckets']) : 0;
		}
		else
		{
			$result = array_column($esquery['aggregations']['punique']['buckets'], 'key');
		}

		return $result;
	}
}
