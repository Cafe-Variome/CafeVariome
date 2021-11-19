<?php namespace App\Libraries\CafeVariome\Query;

use App\Models\Attribute;
use App\Models\AttributeMapping;
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
		$es_index = $this->getESIndexName($source_id);
		$es_client = $this->getESInstance();

		$attribute = $clause['attribute'];
		$attributeObj = $this->getAttribute($attribute, $source_id);
		if ($attributeObj == null)
		{
			// No attribute or mapping for the incoming attribute in the source exists. No need to query ES
			return $iscount ? 0 : [];
		}
		else
		{
			//Query ES
			$attribute = $attributeObj['name'];
			$operator = $clause['operator'];
			$value = $clause['value'];

			$isnot =  (substr($operator,0,6) === 'is not' || $operator === '!=') ? true : false;

			$paramsnew = ['index' => $es_index];
			$paramsnew['body']['query']['bool']['must'][0]['term']['source_id'] = $source_id;
			$arr = [];

			if($isnot){
				$arr['has_child']['query']['bool']['must']['match'] = ['attribute' => $attribute];
				$arr['has_child']['query']['bool']['must_not']['match'] = ['value.raw' => $value];
			}
			else {
				$tmp[]['match'] = ['attribute' => $attribute];

				switch ($operator) {
					case 'is':
					case '=':
						$tmp[]['match'] = ['value.raw' => $value];
						break;
					case 'is like':
					case 'is not like':
						$tmp[]['wildcard'] = ['value.raw' => $value];
						break;
					case '>':
						$tmp[]['range'] = ['value.d' => ['gt' => $value]];
						break;
					case '>=':
						$tmp[]['range'] = ['value.d' => ['gte' => $value]];
						break;
					case '<':
						$tmp[]['range'] = ['value.d' => ['lt' => $value]];
						break;
					case '<=':
						$tmp[]['range'] = ['value.d' => ['lte' => $value]];
						break;
					case 'dt>':
						$tmp[]['range'] = ['value.dt' => ['gt' => $value]];
						break;
					case 'dt>=':
						$tmp[]['range'] = ['value.dt' => ['gte' => $value]];
						break;
					case 'dt<':
						$tmp[]['range'] = ['value.dt' => ['lt' => $value]];
						break;
					case 'dt<=':
						$tmp[]['range'] = ['value.dt' => ['lte' => $value]];
						break;
				}
				$arr['has_child']['query']['bool']['must'] = $tmp;
			}

			$arr['has_child']['type'] = 'eav';

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

			return $result;
		}

//		if ($isnot)
//		{
//			$eavModel = new EAV();
//			$uniqueSubjectIdsArray = $eavModel->getEAVs('subject_id', ['source_id'=> $source_id, 'elastic' => 1], true);
//			$uniqueSubjectIds = [];
//			foreach ($uniqueSubjectIdsArray as $uid)
//			{
//				array_push($uniqueSubjectIds, $uid['subject_id']);
//			}
//
//			$all_ids = ($iscount==true) ? count($uniqueSubjectIds) : $uniqueSubjectIds;
//			$result = $iscount==true ? $all_ids - $result : array_diff($all_ids, $result) ;
//		}

	}

	private function getAttribute(string $attribute, int $source_id)
	{
		// If attribute exists, return it
		$attributeModel = new Attribute();
		$attribute = $attributeModel->getAttributeByNameAndSourceId($attribute, $source_id);
		if ($attribute != null) {
			return $attribute;
		}

		// If attribute mapping exists, return the corresponding atribute
		$attributeMappingModel = new AttributeMapping();
		return $attributeMappingModel->getAttributeByMappingNameAndSourceId($attribute, $source_id);
	}
}
