<?php namespace App\Libraries\CafeVariome\Query;

/**
 * SubjectVariantQuery.php
 * Created 14/07/2021
 *
 * @author Colin Veal
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 *
 */

use App\Libraries\CafeVariome\Entities\Source;

class SubjectVariantQuery extends AbstractQuery
{

	public function __construct()
	{
		$this->aggregate_size = ELASTICSERACH_AGGREGATE_SIZE;
	}

	public function Execute(array $clause, Source $source)
	{
		$es_client = $this->getESInstance();
		$es_index = $source->GetElasticSearchIndexName($this->GetESIndexPrefix());
		$source_id = $source->getID();

		$arr = [];
		foreach ($clause as $key => $value)
		{ // replace with actual parameters
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

		return array_column($esquery['aggregations']['punique']['buckets'], 'key');
	}
}
