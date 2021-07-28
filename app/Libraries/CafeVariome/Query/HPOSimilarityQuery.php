<?php namespace App\Libraries\CafeVariome\Query;


use App\Models\Settings;
use App\Models\Source;

/**
 * HPOSimilarityQuery.php
 * Created 05/06/2021
 *
 * @author Colin Veal
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 *
 */

class HPOSimilarityQuery extends AbstractQuery
{

	public function __construct()
	{

	}

	public function execute(array $clause, int $source_id, bool $iscount)
	{
		$sourceModel = new Source();
		$source_name = $sourceModel->getSourceNameByID($source_id);

		if (array_key_exists('r',$clause))
		{
			$r = $clause['r'];
			$s = $clause['s'];
			$orpha = $clause['ORPHA'];
			$id_str = '';

			foreach ($clause['ids'] as $id)
			{
				$id = strtoupper($id);
				$id_str .= "n.hpoid=\"" . $id . "\" or ";
			}
			$id_str = trim($id_str, ' or ');

			//Do not remove the below line. It's the older way of retrieving similarity data from Neo4J
			//$neo_query = "MATCH (n:HPOterm)-[:REPLACED_BY*0..1]->()-[:SIM_AS*0..10]->()-[r:SIMILARITY]-()<-[:SIM_AS*0..10]-()<-[:REPLACED_BY*0..1]-()-[r2:PHENOTYPE_OF]->(m) where r.rel > $r and m.source = \"" . $source . "\" and (" . $id_str . " ) with m.omimid as omimid, m.subjectid as subjectid, max(r.rel) as maxicm, n.hpoid as hpoid with omimid as omimid, subjectid as subjectid, sum(maxicm) as summax where summax > $s return omimid, subjectid, summax ORDER BY summax DESC";

			//The following way matches the new query builder UI

			$neo_query = "MATCH (n:HPOterm)-[:REPLACED_BY*0..1]->(:HPOterm)<-[:IS_A*0..20]-(:HPOterm)-[:PHENOTYPE_OF*0..1]->(link)-[:PHENOTYPE_OF]->(s:Subject) ";
			if ($orpha == 'true'){
				$neo_query = $neo_query . "where (" . $id_str . ") and s.source = \"" . $source_name . "\" and (link:HPOterm or link:ORPHAterm) ";
			}
			else{
				$neo_query = $neo_query . "where (" . $id_str . ") and s.source = \"" . $source_name . "\" and (link:HPOterm) ";
			}
			$neo_query = $neo_query . "with s.subjectid as subjectid, n.hpoid as hpoid with subjectid as subjectid, count(distinct(hpoid)) as hpoid where hpoid >=  $s  return subjectid, hpoid";
			// $neo_query = "MATCH (n:HPOterm)-[:REPLACED_BY*0..1]->()<-[:IS_A*0..20]-()-[r2:PHENOTYPE_OF]->(m) where (" . $id_str . ") and m.source = \"" . $source . "\" with m.subjectid as subjectid, n.hpoid as hpoid with subjectid as subjectid, count(distinct(hpoid)) as hpoid where hpoid >=  $s  return subjectid, hpoid";
			$pat_ids = [];

			$records = $this->getNeo4JInstance()->runQuery($neo_query);

			foreach ($records as $record) {
				$pat_ids[] = $record->get('subjectid');
			}

			if ($r < 1) {
				$neo_query = "Match (n:HPOterm)-[:REPLACED_BY*0..1]->(:HPOterm)-[:SIM_AS*0..10]->(:HPOterm)-[r:SIMILARITY]-(j:HPOterm) Match (j)<-[:REPLACED_BY*0..1]-(:HPOterm)<-[:IS_A*0..20]-(:HPOterm)-[:PHENOTYPE_OF*0..1]->(link)-[r2:PHENOTYPE_OF]->(s:Subject) ";
				if ($orpha == 'true'){
					$neo_query = $neo_query . "where r.rel >  $r and (" . $id_str . ") and (s.source = \"" . $source_name . "\" and (link:HPOterm or link:ORPHAterm)) ";
				}
				else{
					$neo_query = $neo_query . "where r.rel >  $r and (" . $id_str . ") and (s.source = \"" . $source_name . "\" and (link:HPOterm)) ";
				}

				$neo_query = $neo_query . " with s.subjectid as subjectid, count(distinct(n.hpoid)) as hpoid where hpoid >= $s with hpoid as hpoid, subjectid as subjectid return subjectid, hpoid ORDER BY hpoid DESC";
			}

			$records = $this->getNeo4JInstance()->runQuery($neo_query);

			$pat_ids = [];
			foreach ($records as $record)
			{
				$pat_ids[] = $record->get('subjectid');
			}

			$pat_ids = array_unique($pat_ids);

			if($iscount === true) {
				return $records->count();
			}
			else {
				return $pat_ids;
			}
		}
	}

}
