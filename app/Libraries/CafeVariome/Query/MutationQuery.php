<?php namespace App\Libraries\CafeVariome\Query;

use App\Models\Elastic;
use App\Models\Source;

/**
 * MutationQuery.php
 * Created 13/07/2021
 *
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 *
 */

class MutationQuery extends AbstractQuery
{
	private $aggregate_size;

	public function __construct()
	{
		$this->aggregate_size = ELASTICSERACH_AGGREGATE_SIZE;
	}

	public function execute(array $clause, int $source_id, bool $iscount)
	{
		$elasticModel = new Elastic();
		$sourceModel = new Source();
		$es_client = $this->getESInstance();

		$source_name = $sourceModel->getSourceNameByID($source_id);
		$es_index = $elasticModel->getTitlePrefix() . "_" . $source_id;

		$paramsnew = ['index' => $es_index];
		$paramsnew['body']['query']['bool']['must'][0]['term']['source'] = $source_name . "_eav";

		$glist = $clause['genes'];

		if (!empty($glist)) {
			$genearr = [];
			foreach ($glist as $key => $value) {
				$tmp = [];
				$tmp[]['match'] = ['attribute' => $key];
				$tmp[]['match'] = ['value.raw' => $value];
				$arr_child['has_child']['query']['bool']['must'] = $tmp;
				$arr_child['has_child']['type'] = 'eav';
				$genearr[] = $arr_child;
			}
			$paramsnew['body']['query']['bool']['must'][1]['bool']['must'][]['bool']['should'] = $genearr;
		}

		$mutlist = $clause['mutation'];

		if (!empty($mutlist)) {
			$protaffarr = [];
			foreach ($mutlist as $key => $value) {
				$tmp = [];
				$tmp[]['match'] = ['attribute' => $key];
				$tmp[]['match'] = ['value.raw' => $value];
				$arr_child['has_child']['query']['bool']['must'] = $tmp;
				$arr_child['has_child']['type'] = 'eav';
				$protaffarr[] = $arr_child;
			}
			$paramsnew['body']['query']['bool']['must'][1]['bool']['must'][]['bool']['should'] = $protaffarr;
		}

		$paramsnew['body']['aggs']['punique']['terms'] = ['field' => 'subject_id', 'size' => $this->aggregate_size]; //NEW

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
