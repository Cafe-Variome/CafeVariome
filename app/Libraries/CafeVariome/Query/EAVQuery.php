<?php namespace App\Libraries\CafeVariome\Query;

use App\Models\EAV;
use App\Models\Elastic;
use App\Models\Settings;
use App\Models\Source;
use Elasticsearch\ClientBuilder;

/**
 * EAVQuery.php
 * Created 05/07/2021
 *
 * @author Colin Veal
 * @author Dhiwagaran Thangavelu
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 *
 */

class EAVQuery extends AbstractQuery
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
		$attribute = $clause['attribute'];
		$operator = $clause['operator'];
		$value = $clause['value'];

		$isnot = ($iscount == true && (substr($operator,0,6) === 'is not' || $operator === '!=')) ? true : false;

		$es_index = $elasticModel->getTitlePrefix() . "_" . $source_id;

		$paramsnew = ['index' => $es_index];
		$paramsnew['body']['query']['bool']['must'][0]['term']['source'] = $source_name . "_eav"; // for source

		$tmp[]['match'] = ['attribute' => $attribute];

		switch($operator) {
			case 'is':
			case '=':
			case 'is not':
			case '!=':
				$tmp[]['match'] = ['value.raw' => $value];
			break;
			case 'is like':
			case 'is not like':
				$tmp[]['wildcard'] = ['value.raw' => $value];
			break;
			case '>':
				$tmp[]['range'] = ['value.d' => [ 'gt' => $value]];
				break;
			case '>=':
				$tmp[]['range'] = ['value.d' => [ 'gte' => $value]];
				break;
			case '<':
				$tmp[]['range'] = ['value.d' => [ 'lt' => $value]];
				break;
			case '<=':
				$tmp[]['range'] = ['value.d' => [ 'lte' => $value]];
				break;
			case 'dt>':
				$tmp[]['range'] = ['value.dt' => [ 'gt' => $value]];
				break;
			case 'dt>=':
				$tmp[]['range'] = ['value.dt' => [ 'gte' => $value]];
				break;
			case 'dt<':
				$tmp[]['range'] = ['value.dt' => [ 'lt' => $value]];
				break;
			case 'dt<=':
				$tmp[]['range'] = ['value.dt' => [ 'lte' => $value]];
				break;

		}
		$arr = [];
		$arr['has_child']['type'] = 'eav';
		$arr['has_child']['query']['bool']['must'] = $tmp;
		$paramsnew['body']['query']['bool']['must'][1]['bool']['must'] = $arr;
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

		if ($isnot)
		{
			$eavModel = new EAV();
			$uniqueSubjectIdsArray = $eavModel->getEAVs('subject_id', ['source_id'=> $source_id, 'elastic' => 1], true);
			$uniqueSubjectIds = [];
			foreach ($uniqueSubjectIdsArray as $uid)
			{
				array_push($uniqueSubjectIds, $uid['subject_id']);
			}

			$all_ids = ($iscount==true) ? count($uniqueSubjectIds) : $uniqueSubjectIds;
			$result = $iscount==true ? $all_ids - $result : array_diff($all_ids, $result) ;
		}

		return $result;
	}
}
