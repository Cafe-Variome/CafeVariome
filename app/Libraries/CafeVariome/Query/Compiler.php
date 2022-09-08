<?php namespace App\Libraries\CafeVariome\Query;

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

use App\Libraries\CafeVariome\Entities\Source;
use App\Libraries\CafeVariome\Factory\DiscoveryGroupAdapterFactory;
use App\Libraries\CafeVariome\Factory\SourceAdapterFactory;
use App\Libraries\CafeVariome\Net\NetworkInterface;
use App\Models\Attribute;
use App\Models\Elastic;

class Compiler
{
	private $query;
	private array $uniqueSubjectIds;

	public function __construct()
	{
		$this->uniqueSubjectIds = [];
	}

	public function CompileAndRunQuery(string $query, int $network_id, int $user_id): string
	{
		$attributeModel = new Attribute();
		$discoveryGroupAdapter = (new DiscoveryGroupAdapterFactory())->GetInstance();
		$sourceAdapter = (new SourceAdapterFactory())->GetInstance();

		$session = \Config\Services::session();

		$query = Sanitizer::Sanitize($query);
		$query_array = json_decode($query, 1);

		if(json_last_error() !== JSON_ERROR_NONE) {
			throw new \Exception('Query is not in a correct JSON format.');
		}

		$user_id = ($session->get('user_id') != null) ? $session->get('user_id') : $user_id;

		$userDiscoveryGroupIds = $discoveryGroupAdapter->ReadByUserId($user_id);
		$userDiscoveryGroups = $discoveryGroupAdapter->ReadByIds($userDiscoveryGroupIds);


		$networkDiscoveryGroups = [];

		foreach ($userDiscoveryGroups as &$discoveryGroup)
		{
			if ($discoveryGroup->network_id == $network_id)
			{
				$networkDiscoveryGroups[$discoveryGroup->getID()] = $discoveryGroup;
			}
		}

		$sourcesPolicies = [];

		$discoveryGroupsSourceIds = $discoveryGroupAdapter->ReadAssociatedIdsAndSourceIds(array_keys($networkDiscoveryGroups));

		foreach ($discoveryGroupsSourceIds as $discoveryGroupSourceId)
		{
			if (array_key_exists($discoveryGroupSourceId->discovery_group_id, $networkDiscoveryGroups))
			{
				if (in_array($discoveryGroupSourceId->source_id, $sourcesPolicies))
				{
					if ($sourcesPolicies[$discoveryGroupSourceId->source_id] < $networkDiscoveryGroups[$discoveryGroupSourceId->discovery_group_id]->policy)
					{
						$sourcesPolicies[$discoveryGroupSourceId->source_id] = $networkDiscoveryGroups[$discoveryGroupSourceId->discovery_group_id]->policy;
					}
				}
				else
				{
					$sourcesPolicies[$discoveryGroupSourceId->source_id] = $networkDiscoveryGroups[$discoveryGroupSourceId->discovery_group_id]->policy;
				}
			}
		}

		$attributes = [];
		if (array_key_exists('attributes', $query_array['requires']['response']['components']))
		{
			$attributes = $query_array['requires']['response']['components']['attributes'];
		}

		if (!isset($query_array['query']) || empty($query_array['query']))
		{
			$query_array['query']['components']['matchAll'][0] = [];
		}

		if(!isset($query_array['logic']) || empty($query_array['logic']))
		{
			$this->makeLogic($query_array); // no logic section provided
		}

		$this->query = $query_array;

		$decoupled = $this->decouple($query_array['logic']); // convert to ORs(AND, AND)
		$pointer_query = $this->generate_pointer_query($decoupled);

		$sources = $sourceAdapter->ReadAllOnline();

		$results = [];
		foreach ($sources as $source)
		{
			$source_id = $source->getID();
			if(!array_key_exists($source_id, $sourcesPolicies))
				continue;

			switch($sourcesPolicies[$source_id])
			{
				case DISCOVERY_GROUP_POLICY_EXISTENCE:
					$results[$source->display_name] = [
						'type' => 'existence',
						'payload' =>  'Access Denied',
						'source' => $source
					];
					break;
				case DISCOVERY_GROUP_POLICY_BOOLEAN:
					$networkInterface = new NetworkInterface();
					$thresholdResponse = $networkInterface->GetNetworkThreshold($network_id);
					if ($thresholdResponse->status)
					{
						$network_threshold = $thresholdResponse->data->network_threshold;
						$ids = $this->execute_query($pointer_query, $source_id);
						$idsCount = count($ids);
						$payload = 0;

						if ($idsCount > $network_threshold)
						{
							$payload = $idsCount;
						}
						else if($idsCount <= $network_threshold && $idsCount > 0)
						{
							$payload = true;
						}

						$results[$source->display_name] = [
							'type' => 'boolean',
							'payload' =>  $payload,
							'source' => $source
						];
					}
					break;

				case DISCOVERY_GROUP_POLICY_COUNT:
					$ids = $this->execute_query($pointer_query, $source_id);

					$results[$source->display_name] = [
						'type' => 'count',
						'payload' => count($ids),
						'source' => $source
					];
					break;

				case DISCOVERY_GROUP_POLICY_LIST_WITH_ATTRIBUTES:
					$ids = $this->execute_query($pointer_query, $source_id);

					$records = [];
					$records['subjects'] = $ids;
					$records['attributes'] = [];

					foreach ($attributes as $attribute)
					{
						if (count($ids) > 0) {
							$attributeId = $attributeModel->getAttributeIdByNameAndSourceId($attribute, $source_id);
							$attributeObject = $attributeModel->getAttribute($attributeId);

							if ($attributeObject != null) {
								switch ($attributeObject['storage_location']) {
									case ATTRIBUTE_STORAGE_ELASTICSEARCH:
										$elasticResult = new ElasticsearchResult();
										$records['attributes'][$attribute] = $elasticResult->extract($ids, $attribute, $source_id);
										break;
									case ATTRIBUTE_STORAGE_NEO4J:
										if ($attributeObject['type'] == ATTRIBUTE_TYPE_ONTOLOGY_TERM) {
											$neo4jOntologyResult = new Neo4JOntologyResult();
											$records['attributes'][$attribute] = $neo4jOntologyResult->extract($ids, $attribute, $source_id);
										}
										break;
								}
							}
						}
						else{
							$records['attributes'][$attribute] = [];
						}
					}

					$results[$source->display_name] = [
						'type' => 'list',
						'count' => count($ids),
						'payload' => $records,
						'source' => $source
					];
					break;
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

				if (array_key_exists($pointer, $countCache)) {
					$element[$numor]["$pointer"] = $countCache[$pointer];
				}
				else{
					$element[$numor][] = $pointer;
					$countCache[$pointer] = 0;
				}
			}
			$numor++;
		}

		$outids = []; // final output of ids that match query, return count of this.
		foreach ($element as $current)
		{
			$noids = 0;
			$andids = []; //array of ids for current or statement

			for ($i = 0; $i < count($current); $i++)
			{
				$pointer = $current[$i];
				if ($noids == 1)
				{
					break;
				}
				$lookup = $this->getVal($this->query, $pointer);
				$type = explode('/', $pointer)[2];

				if (array_key_exists($pointer, $idsCache))
				{
					$ids = $idsCache[$pointer];
				}
				else
				{
					$isNot = false;
					if (array_key_exists('operator',$lookup))
					{
						$isNot = substr($lookup['operator'], 0, 6) === 'is not' || $lookup['operator'] === '!=';
					}
					if ($isNot)
					{
						$matchAllQuery = new MatchAllQuery();
						$this->uniqueSubjectIds = $matchAllQuery->execute([], $source_id, false);
					}
					$ids = $this->execute_clause($type, $lookup, $source_id, false);
					$idsCache[$pointer] = $ids;

				}

				if ($i > 0 && count($andids) == 0) // Shortcut to return empty array iff one round of queries has had no results.
				{
					break;
				}

				if (count($andids) > 0)
				{
					$andids = array_intersect($andids, $ids);
					if (count($andids) == 0)
					{
						$noids = 1;
					}
				}
				else
				{
					$andids = $ids;
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
				$eavQuery = new EAVQuery($this->uniqueSubjectIds);
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
}
