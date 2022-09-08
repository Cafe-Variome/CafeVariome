<?php namespace App\Libraries\CafeVariome\Query;

use App\Libraries\CafeVariome\Entities\Source;

/**
 * PhenotypeQuery.php
 * Created 14/07/2021
 *
 * @author Colin Veal
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 *
 */

class PhenotypeQuery extends AbstractQuery
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
		$source_id = $source->getID();

		$operator = $clause['operator'];
		$value = $clause['value'];

		$isnot = substr($operator,0,6) === 'is not' ? true : false;

		switch($operator)
		{
			case 'is':
			case 'is not':
			case '=':
				$tmp[]['match'] = ['value.raw' => strtolower($value)];
			break;
			case 'is like':
			case 'is not like':
				$tmp[]['wildcard'] = ['value.raw' => strtolower($value)];
			break;
		}

		// Elasticsearch query
		$paramsnew = ['index' => $es_index];

		$paramsnew['body']['query']['bool']['must'][0]['term']['source'] = $source_id;
		$paramsnew['body']['query']['bool']['must'][1]['has_child']['type'] = 'eav';
		$paramsnew['body']['query']['bool']['must'][1]['has_child']['query']['bool']['must'] = $tmp;

		$negop = 'must_not';

		if (array_key_exists('negated',$clause) && $clause['negated'] == 'True') $negop = 'must'; //need to add negated to phenotype component

		$paramsnew['body']['query']['bool'][$negop][0]['has_child']['type'] = 'eav';
		$paramsnew['body']['query']['bool'][$negop][0]['has_child']['query']['bool']['must'][0]['match']['attribute'] = 'negated';
		$paramsnew['body']['query']['bool'][$negop][0]['has_child']['query']['bool']['must'][1]['match']['value'] = '1';

		$paramsnew['body']['aggs']['punique']['terms'] = ['field' => 'subject_id', 'size' => $this->aggregate_size];

		$esquery = $es_client->search($paramsnew);

		$result = array_column($esquery['aggregations']['punique']['buckets'], 'key');
		if ($isnot)
		{
			$eavModel = new EAV();
			$uniqueSubjectIdsArray = $eavModel->getEAVs('subject_id', ['source_id'=> $source_id, 'elastic' => 1], true);
			$uniqueSubjectIds = [];
			foreach ($uniqueSubjectIdsArray as $uid)
			{
				array_push($uniqueSubjectIds, $uid['subject_id']);
			}

			$result = array_diff($uniqueSubjectIds, $result) ;
		}

		return $result;
	}
}
