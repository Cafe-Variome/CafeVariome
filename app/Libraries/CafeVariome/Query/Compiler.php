<?php namespace App\Libraries\CafeVariome\Query;

use App\Libraries\ElasticSearch;
use App\Models\EAV;
use App\Models\Elastic;
use App\Models\Settings;
use App\Models\Source;

/**
 * Compiler.php
 * Created 05/07/2021
 *
 * @author Colin Veal
 * @author Owen Lancaster
 * @author Dhiwagaran Thangavelu
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 *
 */

class Compiler
{
	private $query;

	public function __construct()
	{

	}

	public function CompileAndRunQuery(string $query, int $network_key, int $user_id): string
	{
		$sourceModel = new Source();
		$elasticModel = new Elastic();
		$session = \Config\Services::session();
		$setting = Settings::getInstance();
		$hosts = (array)$setting->getElasticSearchUri();
		$elasticSearch = new ElasticSearch($hosts);

		$query = Sanitizer::Sanitize($query);
		$query_array = json_decode($query, 1);

		if(json_last_error() !== JSON_ERROR_NONE) {
			throw new \Exception('Query is not in a correct JSON format.');
		}

		$installation_key = $setting->getInstallationKey();
		$user_id = ($session->get('user_id') != null) ? $session->get('user_id') : $user_id;

		$master_group_sources = $this->getNetworkGroups($user_id, $network_key, $installation_key, 'master');
		$source_display_group_sources = $this->getNetworkGroups($user_id, $network_key, $installation_key, 'source_display');
		$count_display_group_sources = $this->getNetworkGroups($user_id, $network_key, $installation_key, 'count_display');

		$attributes = [];
		if (array_key_exists('attributes', $query_array['requires']['response']['components'])){
			$attributes = $query_array['requires']['response']['components']['attributes'];
		}

		if (!isset($query_array['query']) || empty($query_array['query'])) {
			$query_array['query']['components']['matchAll'][0] = [];
		}

		if(!isset($query_array['logic']) || empty($query_array['logic'])) {
			$this->makeLogic($query_array); // no logic section provided
		}

		$this->query = $query_array;

		$decoupled = $this->decouple($query_array['logic']); // convert to ORs(AND, AND)
		$pointer_query = $this->generate_pointer_query($decoupled);

		$sources = $sourceModel->getOnlineSources();

		$results = [];
		foreach ($sources as $source) {

			$elasticIndexName = $elasticModel->getTitlePrefix() . "_" . $source['source_id'];
			if ($elasticSearch->indexExists($elasticIndexName))
			{
				$source_id = $source['source_id'];
				if(!in_array($source_id, $master_group_sources) && !in_array($source_id, $source_display_group_sources) && !in_array($source_id, $count_display_group_sources)) continue;

				$results[$source['name']]['records']['subjects'] = "Access Denied";
				if (array_key_exists($source_id, $source_display_group_sources) || array_key_exists($source_id, $count_display_group_sources))
				{
					$ids = $this->execute_query($pointer_query, $source['source_id']);
					$records = [];
					$records['subjects'] = $ids;

					$elasticResult = new ElasticsearchResult();
					foreach ($attributes as $attribute){
						$records['attributes'][$attribute] = $elasticResult->extract($ids, $attribute, $source_id);
					}

					$results[$source['name']] = [
						'records' => $records,
						'source_display' => array_key_exists($source_id, $source_display_group_sources),
						'details' => $source
					];
				}
			}
		}

		return json_encode($results);
	}

	private function execute_query(string $pointer_query, int $source_id): array
	{
		$countCache = [];
		$idsCache = [];
		$element = [];
		$orarray = explode(") OR (", $pointer_query); //given that we convert all querys into a series of ors we can start by splitting them. (A and (B or c) = (A and B) or (A and C))

		// first step is to create counts for each core component search (the bits in '[]') so we know which order to collect IDs as this will reduce memory use or scan and scroll in ES
		$numor = 0; // which or we are processing, start at first element in array
		foreach ($orarray as $and)
		{
			//each or element should be an and statement note that we are using brackets to distinguish elements that need to be queried together, i.e [chr:1 and pos:124]
			$andarray = explode("] AND [", $and); //create array of '[]' elements
			foreach ($andarray as $pointer)
			{
				// here we are going to query each '[]' and keep counts for each one.
				//remove any parantheses or brackets
				$pointer = trim($pointer, "()[]/");
				$type = explode('/', $pointer)[2];
				$clause = $this->getVal($this->query, $pointer);

				if (array_key_exists($pointer, $countCache)) {
					$element[$numor]["$pointer"] = $countCache[$pointer];
				}
				else{
					$qCount = $this->execute_clause($type, $clause, $source_id, true);
					$element[$numor]["$pointer"] = $qCount;
					$countCache[$pointer] = $qCount;
				}
			}
			$numor++;
		}

		$outids = []; // final output of ids that match query, return count of this.
		foreach ($element as $current) {
			$noids = 0;
			asort($current); //sort the counts in an or statement so only need to keep array of smallest number of ids
			if (reset($current) == 0) continue; // if smallest is 0 then no need to continue as answer is 0
			$andids=[]; //array of ids for current or statement

			foreach ($current as $pointer => $val){
				if ($noids == 1) {
					break;
				}
				$lookup = $this->getVal($this->query, $pointer);
				$type = explode('/', $pointer)[2];

				if (array_key_exists('operator',$lookup) === false || (substr($lookup['operator'],0,6) !== 'is not' && $lookup['operator'] !== '!=')){
					// IS
					if (array_key_exists($pointer, $idsCache)) {
						$ids = $idsCache[$pointer];
					}
					else{
						$ids = $this->execute_clause($type, $lookup, $source_id, false);
						$idsCache[$pointer] = $ids;
					}

					if (count($andids) > 0){
						$andids = array_intersect($andids, $ids);
						if (count($andids) == 0){
							$noids = 1;
						}
					}
					else{
						$andids = $ids;
					}
				}
			}

			foreach ($current as $pointer => $val){
				if ($noids == 1) {
					break;
				}
				$type = explode('/', $pointer)[2];

				$lookup = $this->getVal($this->query, $pointer);
				if (array_key_exists('operator',$lookup) === true && (substr($lookup['operator'],0,6) === 'is not' || $lookup['operator'] === '!=')){
					// IS NOT
					$ids = $this->execute_clause($type, $lookup, $source_id,false);

					if (count($andids) > 0){
						$andids = array_values(array_diff($andids, $ids));
						if (count($andids) == 0){
							$noids = 1;
						}
					}
					else{
						$eavModel = new EAV();
						$uniqueSubjectIdsArray = $eavModel->getEAVs('subject_id', ['source_id'=> $source_id, 'elastic' => 1], true);
						$uniqueSubjectIds = [];
						foreach ($uniqueSubjectIdsArray as $uid) {
							array_push($uniqueSubjectIds, $uid['subject_id']);
						}
						$andids = array_values(array_diff($uniqueSubjectIds, $ids));
					}
				}
			}

			$outids = array_unique(array_merge($outids, $andids), SORT_REGULAR);
		}

		return $outids;
	}

	private function makeLogic(array &$query)
	{
		$query['logic'] = ['-AND' => []];
		foreach ($query['query']['components'] as $component => $arr)
		{
			if(!empty($arr)) {
				$cnt = 0;
				foreach ($arr as $entry)
					array_push($query['logic']['-AND'], "/query/components/$component/" . $cnt++);
			}
		}
	}

	private function decouple(array $arr): string
	{
		reset($arr);
		$type = key($arr);

		if($type !== 0) {
			$arr = $arr[$type];
		}

		$str = '';
		foreach ($arr as $key => $el) {
			if(is_array($el)) {
				if($this->is_assoc($el)) {
					$out = $this->decouple($el);
					if($type === '-AND') {
						$str = $this->merge_arrays_to_string($this->split_string_to_array($str), $this->split_string_to_array($out));
					}
					else {
						$str = implode(',', array_merge($this->split_string_to_array($str), $this->split_string_to_array($out)));
					}
				}
				else {
					return implode($type === '-AND' ? '' : ',', $el);
				}
			}
			else {
				if($type === '-AND') {
					$str = $this->merge_arrays_to_string($this->split_string_to_array($str), [$el]);
				}
				else {
					$str = implode(',', array_merge($this->split_string_to_array($str), [$el]));
				}
			}
		}

		return trim($str, ',');
	}

	private function generate_pointer_query($result): string
	{
		$qStr = '';
		foreach (explode(',', $result) as $expOR) {
			$oStr = '';
			foreach (explode('|', $expOR) as $expAND) {
				$aStr = $expAND;
				$aStr = '[' . trim($aStr, ' AND ') . ']';
				$oStr .= $aStr . ' AND ';
			}
			$oStr = trim($oStr, ' AND ');
			$qStr .= "($oStr) OR ";
		}

		return trim($qStr, ' OR ');
	}

	private function is_assoc(array $arr): bool
	{
		foreach ($arr as $key => $val)
		{
			if (is_array($val)) return true;
		}

		return false;
	}

	private function merge_arrays_to_string(array $arr1, array $arr2): string
	{
		if(empty($arr1)) $arr = $arr2;
		elseif(empty($arr2)) $arr = $arr1;
		else foreach($arr1 as $a1) foreach($arr2 as $a2) $arr[] = $a1 . '|' . $a2;
		return implode(',', $arr);
	}

	private function split_string_to_array(string $str)
	{
		return preg_split('/,/',$str, NULL, PREG_SPLIT_NO_EMPTY);
	}

	private function getVal($jex, string $path)
	{
		$pArr = explode('/', ltrim($path, '/'));
		$path = "['" . implode("']['", $pArr) . "']";
		return eval("return \$jex{$path};");
	}

	private function getNetworkGroups(int $user_id, int $network_key, string $installation_key, string $access_type): array
	{
		$sourceModel = new Source();
		$sources_array = $sourceModel->getSourcesByUserIdAndNetworkKey($user_id, $installation_key, $network_key, $access_type);

		$sids = [];
		if(!array_key_exists('error', $sources_array)) {
			foreach ($sources_array as $s) {
				$sids[$s['source_id']] = $s['source_id'];
			}
		}
		return $sids;
	}

	private function execute_clause(string $type, array $clause, int $source_id, bool $iscount)
	{
		switch (strtolower($type))
		{
			case 'eav':
				$eavQuery = new EAVQuery();
				return $eavQuery->execute($clause, $source_id, $iscount);
			case 'sim':
				$HPOSimilarityQuery = new HPOSimilarityQuery();
				return $HPOSimilarityQuery->execute($clause, $source_id, $iscount);
			case 'ordo':
				$ORPHASimilarityQuery = new ORPHASimilarityQuery();
				return $ORPHASimilarityQuery->execute($clause, $source_id, $iscount);
			case 'matchall':
				$matchAllQuery = new MatchAllQuery();
				return $matchAllQuery->execute($clause, $source_id, $iscount);
			case 'phenotype':
				$phenotypeQuery = new PhenotypeQuery();
				return $phenotypeQuery->execute($clause, $source_id, $iscount);
			case 'subjectvariant':
				$subjectvariantQuery = new SubjectVariantQuery();
				return $subjectvariantQuery->execute($clause, $source_id, $iscount);
			case 'mutation':
				$mutationQuery = new MutationQuery();
				return $mutationQuery->execute($clause, $source_id, $iscount);
		}
	}

	private function elasticVal (array $ids, string $attribute, string $source, $flag = 'id') {
		$elasticModel = new Elastic();
		$sourceModel = new Source();

		$paramsnew = [];
		$sourceId = $sourceModel->getSourceIDByName($source);
		$es_index = $elasticModel->getTitlePrefix() . "_" . $sourceId;
		$paramsnew = ['index' => $es_index];

		$paramsnew['size'] = 10000;
		//$paramsnew['type'] = 'subject';
		$paramsnew['body']['query']['bool']['must'][0]['term']['source'] = $source . '_eav'; // for source
		$paramsnew['body']['query']['bool']['must'][1]['term']['type'] = "eav";
		$paramsnew['body']['query']['bool']['must'][2]['term']['attribute'] = $attribute;
		foreach ($ids as $id) {
			$paramsnew['body']['query']['bool']['should'][] = ['term'=>['subject_id' => $id]];
		}

		$paramsnew['body']['query']['bool']["minimum_should_match"] = 1;

		$jp = json_encode($paramsnew);

		$resultsnew = $this->elasticClient->search($paramsnew);

		$final = [];
		foreach ($resultsnew['hits']['hits'] as $hit) {
			$id =  $hit['_source']['subject_id'];
			$val =  $hit['_source']['value'];
			$final[$flag === 'value' ? $val : $id][] = $flag === 'value' ? $id : $val;
		}
		foreach ($final as $key => $value) {
			$final[$key] = array_unique($final[$key]);
		}
		return $final;

	}
}
