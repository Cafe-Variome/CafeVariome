<?php namespace App\Libraries\CafeVariome\Query;

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

use App\Libraries\CafeVariome\Entities\Source;

class EAVQuery extends AbstractQuery
{
	private $aggregate_size;
	protected array $uniqueSubjectIds;

	public function __construct(array $uniqueSubjectIds)
	{
		$this->aggregate_size = ELASTICSERACH_AGGREGATE_SIZE;
		$this->uniqueSubjectIds = $uniqueSubjectIds;
	}

	public function Execute(array $clause, Source $source)
	{
		$es_client = $this->getESInstance();
		$es_index = $source->GetElasticSearchIndexName($this->GetESIndexPrefix());
		$source_id = $source->getID();
		$attribute = $clause['attribute'];
		$operator = $clause['operator'];
		$isnot =  (substr($operator,0,6) === 'is not' || $operator === '!=') ? true : false;

		$attributeObj = $this->getAttribute($attribute, $source_id);

		if ($attributeObj->isNull())
		{
			// No attribute or mapping for the incoming attribute in the source exists. No need to query ES.
			return [];
		}
		else
		{
			$attribute = $attributeObj->name;
			$attributeId = $attributeObj->getID();

			$value = $clause['value'];

			$valueObj = $this->getValue($value, $attributeId);
			if (
				($operator == 'is' || $operator == 'is not' || $operator == '=' || $operator == '!=') && // Take this path only if the query is an exact match.
				$valueObj == null
			)
			{
				// No value or mapping for the incoming value in the source exists. No need to query ES.
				return [];
			}
			else
			{
				if ($valueObj != null)
				{
					$value = $valueObj['name'];
				}

				//Query ES
				$arr = [];
				$paramsnew = ['index' => $es_index];
				$paramsnew['body']['query']['bool']['must'][0]['term']['source_id'] = $source_id;
				$tmp[]['match'] = ['attribute' => $attribute];

				switch ($operator) {
					case 'is':
					case 'is not':
					case '=':
					case '!=':
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
				$arr['has_child']['type'] = 'eav';
				$paramsnew['body']['query']['bool']['must'][1]['bool']['must'] = $arr;
				$paramsnew['body']['aggs']['punique']['terms'] = ['field' => 'subject_id', 'size' => $this->aggregate_size];

				$esquery = $es_client->search($paramsnew);

				$result = array_column($esquery['aggregations']['punique']['buckets'], 'key');

				if ($isnot)
				{
					$all_ids = $this->uniqueSubjectIds;
					$result = array_diff($all_ids, $result) ;
				}

				return $result;
			}
		}
	}
}
