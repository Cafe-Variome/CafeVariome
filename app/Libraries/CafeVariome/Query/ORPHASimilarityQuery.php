<?php namespace App\Libraries\CafeVariome\Query;


use App\Models\Source;
use Laudis\Neo4j\Types\CypherList;

/**
 * ORPHASimilarityQuery.php
 * Created 05/06/2021
 *
 * @author Colin Veal
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 *
 */

class ORPHASimilarityQuery extends AbstractQuery
{
	public function __construct()
	{

	}

	public function execute(array $clause, int $source_id, bool $iscount)
	{
		$sourceModel = new Source();
		$sourceUID = $sourceModel->getSourceUID($source_id);

		if (array_key_exists('r',$clause))
		{
			$r = $clause['r'];
			$s = $clause['s'];
			$hpo = $clause['HPO'];
			$orpha_id = $clause['id'][0];

			// if just orpha
			if($r == 1 && $s == 100 && $hpo == 'false')
			{
				$neo_query = "Match (o:ORPHAterm{orphaid:\"" . $orpha_id . "\"})<-[:IS_A*0..20]-(:ORPHAterm)-[:PHENOTYPE_OF]-(s) where s.source_id = \"" . $source_id . "\" and s.uid = \"" . $sourceUID . "\" return s.subjectid as subjectid";
				$records = $this->getNeo4JInstance()->runQuery($neo_query);
				$pat_ids = [];
				foreach ($records as $record)
				{
					$pat_ids[] = $record->get('subjectid');
				}

				if($iscount === true)
				{
					return count($pat_ids);
				} else
				{
					return $pat_ids;
				}
			}
			else
			{
				$ids = [];
				$orphatotals = [];
				$subjects = [];
				$IC = False;
				$starttime = microtime(true);
				//get totals
				$neo_query = $this->create_neo($source_id, $sourceUID, $orpha_id, 1, 0, false, true, false);
				$records = $this->getNeo4JInstance()->runQuery($neo_query);
				foreach ($records as $record) {
					$orphatotals[$record->get('ORPHA')] = [
						'hpo' => $record->get('LINK'),
						'FC' => $record->get('FrequencyCode'),
						'branches' => $record->get('PA_Branches'),
						'omimic' => $record->get('OMIM_IC'),
						'orphaic' => $record->get('ORPHA_IC'),
						'branch_hpos' => $record->get('ahs')
					];
				}
				// error_log(count($orphatotals));
				$neo_query = $this->create_neo($source_id, $sourceUID, $orpha_id, 1, 0, false, true, true);
				$records = $this->getNeo4JInstance()->runQuery($neo_query);
				foreach ($records as $record) {
					if (array_key_exists($record->get('ORPHA'),$orphatotals))
					{
						$orp = $orphatotals[$record->get('ORPHA')]['orphaic'];
						$om = $orphatotals[$record->get('ORPHA')]['omimic'];
						$orpic = $record->get('ORPHA_IC');
						$omic = $record->get('OMIM_IC');

						if (bccomp($orp, $orpic) != 0 || bccomp($om, $omic) != 0)
						{
							$orphatotals[$record->get('ORPHA')]['hpo'] = $record->get('LINK');
							$orphatotals[$record->get('ORPHA')]['FC'] = $record->get('FrequencyCode');
							$orphatotals[$record->get('ORPHA')]['omimic'] = $record->get('OMIM_IC');
							$orphatotals[$record->get('ORPHA')]['orphaic'] = $record->get('ORPHA_IC');
							$IC = True;
						}
					}
					else
					{
						$orphatotals[$record->get('ORPHA')] = [
							'hpo' => $record->get('LINK'),
							'FC' => $record->get('FrequencyCode'),
							'branches' => 0,
							'omimic' => $record->get('OMIM_IC'),
							'orphaic' => $record->get('ORPHA_IC'),
							'branch_hpos' => []];
						$IC = True;
					}
				}
				// Code to process the above results in a more easily queryable array.
				$orpha_ids = array_keys($orphatotals);
				$ic_counts = [];
				$new = [];
				foreach ($orpha_ids as $orpha_id) {
					$new[$orpha_id] = [];
					$ic_counts[$orpha_id] = ['ob_sum' => 0,'ex_sum' => 0,'orpha_sum' => 0, 'omim_sum' => 0,"ob_terms" => [],'ex_terms' => []];
					foreach ($orphatotals[$orpha_id]['hpo'] as $data) {
						$new[$orpha_id]['hpo'][$data[0]] = [];
						$new[$orpha_id]['hpo'][$data[0]]['FC'] = $data[1];
						$new[$orpha_id]['hpo'][$data[0]]['omimic'] = $data[2];
						$new[$orpha_id]['hpo'][$data[0]]['orphaic'] = $data[3];
						if ($new[$orpha_id]['hpo'][$data[0]]['FC'] == "EX") {
							$ic_counts[$orpha_id]['ex_sum'] = $ic_counts[$orpha_id]['ex_sum'] + $new[$orpha_id]['hpo'][$data[0]]['orphaic'];
							$ic_counts[$orpha_id]['ex_terms'][] = $data[0];
						}
						elseif ($new[$orpha_id]['hpo'][$data[0]]['FC'] == "OB") {
							$ic_counts[$orpha_id]['ob_sum'] = $ic_counts[$orpha_id]['ob_sum'] + $new[$orpha_id]['hpo'][$data[0]]['orphaic'];
							$ic_counts[$orpha_id]['ob_terms'][] = $data[0];
							$ic_counts[$orpha_id]['orpha_sum'] = $ic_counts[$orpha_id]['orpha_sum'] + $new[$orpha_id]['hpo'][$data[0]]['orphaic'];
							$ic_counts[$orpha_id]['omim_sum'] = $ic_counts[$orpha_id]['omim_sum'] + $new[$orpha_id]['hpo'][$data[0]]['omimic'];
						}
						else {
							$ic_counts[$orpha_id]['orpha_sum'] = $ic_counts[$orpha_id]['orpha_sum'] + $new[$orpha_id]['hpo'][$data[0]]['orphaic'];
							$ic_counts[$orpha_id]['omim_sum'] = $ic_counts[$orpha_id]['omim_sum'] + $new[$orpha_id]['hpo'][$data[0]]['omimic'];
						}
					}
					$new[$orpha_id]['branches'] = $orphatotals[$orpha_id]['branches'];
					$new[$orpha_id]['branch_hpos']  = $orphatotals[$orpha_id]['branch_hpos'];
				}
				error_log(print_r($new,1));
				// error_log(print_r($ic_counts,1));
				// return;
				// error_log(print_r(array_keys($orphatotals),1));

				$omimic_min = [];
				$orphaic_min = [];
				unset($orphatotals);
				foreach ($ic_counts as $o => $o_value){
					// error_log(print_r($o_value,1));
					$omimic_min[] = $o_value['omim_sum'];
					$orphaic_min[] = $o_value['orpha_sum'];
				}
				// error_log(print_r($omimic_min,1));
				// error_log(print_r($orphaic_min,1));


				//problem with infinity values....
				$ICLIM = $s/100 * count($orphaic_min) > 0 ? min($orphaic_min) : 0;
				// error_log($ICLIM);
				// return;

				// return;
				// use this code to merge, dosim first
				//$r=1;

				// IC set to true until we need to use branches.
				$IC = true;

				$run = true;
				while ($run)
				{
					//Get exact matches
					// if($r == 1 && $hpo == 'true'){
					//     error_log("NO SIM HPO and ORHPA");
					//     $neo_query = $this->create_neo($source_id, $sourceUID, $orpha_id, 1, $ICLIM, true, false, false);
					// }
					// elseif($r == 1 && $hpo == 'false'){
					//     error_log("NO SIM ORPHA");
					//     $neo_query = $this->create_neo($source_id, $sourceUID, $orpha_id, 1, $ICLIM, false, false, false);

					// }
					// elseif($r < 1 && $hpo == 'true'){
					//     error_log("SIM HPO and ORPHA");
					//     $neo_query = $this->create_neo($source_id, $sourceUID, $orpha_id, $r, $ICLIM, true, false, false);
					// }
					// elseif($r < 1 && $hpo == 'false'){
					//     error_log("SIM ORPHA only");
					//     $neo_query = $this->create_neo($source_id, $sourceUID, $orpha_id, $r, $ICLIM, false, false, false);
					// }

					// $records = $this->getNeo4JInstance()->runQuery($neo_query);
					// $subjects = [];
					// foreach ($records as $record) {
					//     $subject_id = $record->get('subjectid');
					//     $orpha = $record->get('orphaid');
					//     $subject_hpo = $record->get('subjects_hpoterms');
					//     $orpha_hpo = $record->get('orphas_hpoterms');
					//     $subjects[$subject_id][$orpha] = [
					//         'subject_hpoterms' => $subject_hpo,
					//         'orphas_hpoterms' => $orpha_hpo];
					// }



					if ($IC  == True){
						if($r == 1 && $hpo == 'true'){
							error_log("IC NO SIM HPO and ORHPA");
							$neo_query = $this->create_neo($source_id, $sourceUID, $orpha_id, $r, $ICLIM, true, False,True);
						}
						elseif($r == 1 && $hpo == 'false'){
							error_log("IC NO SIM ORPHA");
							$neo_query = $this->create_neo($source_id, $sourceUID, $orpha_id, $r, $ICLIM, 1, False, True);

						}
						elseif($r < 1 && $hpo == 'true'){
							error_log("IC SIM HPO and ORPHA");
							$neo_query = $this->create_neo($source_id, $sourceUID, $orpha_id, $r, $ICLIM, True, False, True);
						}
						elseif($r < 1 && $hpo == 'false'){
							error_log("IC SIM ORPHA only");
							$neo_query = $this->create_neo($source_id, $sourceUID, $orpha_id, $r, $ICLIM, False, False, True);

						}

						$records = $this->getNeo4JInstance()->runQuery($neo_query);

						foreach ($records as $record) {
							$subject_id = $record->get('subjectid');
							$orpha = $record->get('orphaid');
							$subject_hpo = $record->get('subjects_hpoterms');
							$orpha_hpo = $record->get('orphas_hpoterms');

							if (array_key_exists($subject_id,$subjects) && array_key_exists($orpha,$subjects[$subject_id])){
								$subjects[$subject_id][$orpha]['subject_hpoterms'] = array_unique(array_merge($this->CypherToArray($subjects[$subject_id][$orpha]['subject_hpoterms']), $this->CypherToArray($subject_hpo)));
								$subjects[$subject_id][$orpha]['orphas_hpoterms'] = array_unique(array_merge($this->CypherToArray($subjects[$subject_id][$orpha]['orphas_hpoterms']), $this->CypherToArray($orpha_hpo)));

							}
							else{
								$subjects[$subject_id][$orpha] = [
									'subject_hpoterms' => $subject_hpo,
									'orphas_hpoterms' => $orpha_hpo];
							}
						}
					}
					if ($r == 1){
						$run = False;
					}
					$r = 1;
				}
				$excluded = [];
				foreach ($subjects as $id => $sub) {
					foreach ($sub as $oid => $orpha) {
						// error_log($oid);
						// return;
						// error_log(print_r($orpha,1));
						$orpha_hpoterms = $orpha['orphas_hpoterms'];
						if (!is_array($orpha_hpoterms))
						{
							$orpha_hpoterms = $this->CyphertoArray($orpha_hpoterms);
						}
						if (count(array_intersect($orpha_hpoterms, $ic_counts[$oid]['ex_terms'])) > 0)
						{
							unset($subjects[$id]);
						}
						elseif (count(array_intersect($orpha_hpoterms, $ic_counts[$oid]['ob_terms'])) >= count($ic_counts[$oid]['ob_terms'])) {
							$ic = 0;
							$unique_orpha_hpoterms = array_unique($orpha_hpoterms);
							foreach ($unique_orpha_hpoterms as $hpo_term) {
								if (array_key_exists($hpo_term, $new[$oid]['hpo']))
								{
									$ic = $ic + $new[$oid]['hpo'][$hpo_term]['orphaic'];
								}
							}
							if (bccomp("$ICLIM", "$ic") === 1)
							{
								unset($subjects[$id]);
							}
						}
						else {
							unset($subjects[$id]);
						}
					}

				}

				// // for each subject check ob in link for orpha against orpha tot link, same for ex and min IC otherwise unset
				// foreach ($subjects as $id => $sub) {
				//     foreach ($sub as $oid => $orpha) {
				//         if ($orpha['orphaic'] < ($orphatotals[$oid]['orphaic'] * ($s/100))) {
				//             unset($subjects[$id]);
				//         }
				//         else{
				//             foreach ($orphatotals[$oid]['hpo'] as $h){
				//                 $key = '';
				//                 if (($h[1] == 'OB' and array_search($h[0], array_column($subjects[$id][$oid]['hpo'],0)) == '')  || $h[1] == 'OB' and array_search($h[0], array_column($subjects[$id][$oid]['hpo'],0)) != ''){
				//                     unset($subjects[$id]);

				//                 }
				//             }
				//         }
				//     }
				// }
			}

			$pat_ids = array_keys($subjects);
			error_log(count(array_keys($subjects)));
			$endtime = microtime(true);
			$timediff = $endtime - $starttime;
			error_log($timediff);

			if($iscount === true)
			{
				return count($pat_ids);
			}
			else
			{
				return $pat_ids;
			}
		}
	}

	private function create_neo(int $source_id, string $sourceUID, string $orpha_id, $r = 1, $ICLIM = 0, $hpo = false, $total = true, $IC = false): string
	{
		$neo_query = "";
		if ($IC == false)
		{
			//branches + IC
			if ($total == false)
			{
				// Query 3
				if ($r == 1 && $hpo == true)
				{
					$neo_query = 'Match (oo:ORPHAterm{orphaid:"' . $orpha_id . '"})<-[:IS_A*0..20]-(o:ORPHAterm)<-[ob:PHENOTYPE_OF]-(oh:HPOterm)
									with collect(distinct(oh)) as coh, o, ob
									unwind coh as oh
									match (oh)-[:IS_A*0..100]->(ah:HPOterm)-[:IS_A]->(ab:HPOterm {hpoid:"HP:0000118"})
									with o,oh,ah
									Match (oh)<-[:IS_A*0..20]-(ph:HPOterm)-[:PHENOTYPE_OF*0..1]->(link)-[:PHENOTYPE_OF]->(s:Subject)
									where s.source_id = "' . $source_id . '" and s.uid = "' . $sourceUID . '" and  (link:HPOterm or link:ORPHAterm) ';
				}
				// Query 4
				elseif ($r == 1 && $hpo == false)
				{
					$neo_query = 'Match (oo:ORPHAterm{orphaid:"' . $orpha_id . '"})<-[:IS_A*0..20]-(o:ORPHAterm)<-[ob:PHENOTYPE_OF]-(oh:HPOterm)
									with collect(distinct(oh)) as coh, o, ob
									unwind coh as oh
									match (oh)-[:IS_A*0..100]->(ah:HPOterm)-[:IS_A]->(ab:HPOterm {hpoid:"HP:0000118"})
									with o,oh,ah
									Match (oh)<-[:IS_A*0..20]-(ph:HPOterm)-[:PHENOTYPE_OF*0..1]->(link:ORPHAterm)-[:PHENOTYPE_OF]->(s:Subject)
									WHERE s.source_id = "' . $source_id . '" and s.uid = "' . $sourceUID . '" ';
				}
				// Query 5
				elseif ($r < 1 && $hpo == true)
				{
					$neo_query = 'Match (oo:ORPHAterm{orphaid:"' . $orpha_id . '"})<-[:IS_A*0..20]-(o:ORPHAterm)<-[ob:PHENOTYPE_OF]-(oh:HPOterm)-[:REPLACED_BY*0..1]->(:HPOterm)-[r:SIMILARITY]-(j:HPOterm)
									using index r:SIMILARITY(rel)
									where r.rel >= ' . $r . '
									with distinct(j) as dj,oh,o
									match (oh)-[:IS_A*0..100]->(ah:HPOterm)-[:IS_A]->(ab:HPOterm {hpoid:"HP:0000118"})
									with dj,oh,o,ah
									Match (dj)<-[:REPLACED_BY*0..1]-(:HPOterm)<-[:IS_A*0..20]-(ph:HPOterm)
									with distinct(ph) as dph,oh, o
									Match (dph)-[:PHENOTYPE_OF*0..1]->(link)-[r2:PHENOTYPE_OF]->(s:Subject)
									WHERE s.source_id = "' . $source_id . '" and s.uid = "' . $sourceUID . '" and (link:HPOterm or link:ORPHAterm) ';
				}
				// Query 6
				elseif ($r < 1 && $hpo == false)
				{
					$neo_query = 'Match (oo:ORPHAterm{orphaid:"' . $orpha_id . '"})<-[:IS_A*0..20]-(o:ORPHAterm)<-[ob:PHENOTYPE_OF]-(oh:HPOterm)-[:REPLACED_BY*0..1]->(:HPOterm)-[r:SIMILARITY]-(j:HPOterm)
									using index r:SIMILARITY(rel)
									where r.rel >= ' . $r . '
									with distinct(j) as dj,oh,o
									match (oh)-[:IS_A*0..100]->(ah:HPOterm)-[:IS_A]->(ab:HPOterm {hpoid:"HP:0000118"})
									with dj,oh,o,ah
									Match (dj)<-[:REPLACED_BY*0..1]-(:HPOterm)<-[:IS_A*0..20]-(ph:HPOterm)-[:PHENOTYPE_OF*0..1]->(s:ORPHAterm)-[r2:PHENOTYPE_OF]->(s:Subject)
									WHERE s.source_id = "' . $source_id . '" and s.uid = "' . $sourceUID . '" ';
				}

				$neo_query = $neo_query . 'return s.subjectid as subjectid,o.orphaid as orphaid, collect(oh.hpoid) as orphas_hpoterms, collect(ph.hpoid) as subjects_hpoterms order by s.subjectid';
			}
			// First call Query
			else
			{
				$neo_query = $neo_query . 'Match (oo:ORPHAterm{orphaid:"' . $orpha_id . '"})<-[:IS_A*0..20]-(o:ORPHAterm)<-[ob:PHENOTYPE_OF]-(oh:HPOterm) with collect(distinct(oh)) as coh, o, ob unwind coh as oh match (oh)-[:IS_A*0..100]->(ah:HPOterm)-[:IS_A]->(ab:HPOterm {hpoid:"HP:0000118"}) ';
				$neo_query = $neo_query . 'with o, collect(distinct(oh)) as coh, count(distinct(oh)) as ohn, ob.frequencycode as fc,collect(distinct([oh.hpoid,ob.frequencycode,oh.ICvsOMIM, oh.ICvsORPHA])) as hlink, collect(distinct(ah.hpoid)) as cah unwind coh as oh with  o,hlink,fc as FrequencyCode, sum(oh.ICvsOMIM) as OMIM_IC, sum(oh.ICvsORPHA) as ORPHA_IC,cah, ohn with o, collect(hlink) as link, collect([FrequencyCode,OMIM_IC, ORPHA_IC]) as FrequencyCode, sum(OMIM_IC) as OMIM_IC, sum(ORPHA_IC) as ORPHA_IC,collect(cah) as hcah, sum(ohn) as ohn unwind link as hlink unwind hlink as olink unwind hcah as cah unwind cah as ah return o.orphaid as ORPHA, collect(distinct(olink)) as LINK, FrequencyCode, ORPHA_IC, OMIM_IC, collect(distinct(ah)) as ahs, ohn as MATCHN, count(distinct(ah)) as PA_Branches';
			}
		}
		else
		{
			//IC (might not need hpos)
			if ($total == false)
			{
				// Query 7
				if ($r == 1 && $hpo == true)
				{
					$neo_query = 'Match (oo:ORPHAterm{orphaid:"' . $orpha_id . '"})<-[:IS_A*0..20]-(o:ORPHAterm)<-[ob:PHENOTYPE_OF]-(oh:HPOterm)
									with oh,o
									Match (oh)<-[:IS_A*0..20]-(ph:HPOterm)-[:PHENOTYPE_OF*0..1]->(link)-[:PHENOTYPE_OF]->(s:Subject)
									WHERE s.source_id = "' . $source_id . '" and s.uid = "' . $sourceUID . '" and (link:HPOterm or link:ORPHAterm) ';
				}
				// Query 8
				elseif ($r == 1 and $hpo == false)
				{
					$neo_query = 'Match (oo:ORPHAterm{orphaid:"' . $orpha_id . '"})<-[:IS_A*0..20]-(o:ORPHAterm)<-[ob:PHENOTYPE_OF]-(oh:HPOterm)
									with oh,o
									Match (oh)<-[:IS_A*0..20]-(ph:HPOterm)-[:PHENOTYPE_OF*0..1]->(link:ORPHAterm)-[:PHENOTYPE_OF]->(s:Subject)
									WHERE s.source_id = "' . $source_id . '" and s.uid = "' . $sourceUID . '" ';
				}
				// Query 9
				elseif ($r < 1 and $hpo == true)
				{
					$neo_query = 'Match (oo:ORPHAterm{orphaid:"' . $orpha_id . '"})<-[:IS_A*0..20]-(o:ORPHAterm)<-[ob:PHENOTYPE_OF]-(oh:HPOterm)-[:REPLACED_BY*0..1]->(:HPOterm)-[r:SIMILARITY]-(j:HPOterm)
									using index r:SIMILARITY(rel)
									where r.rel >= ' . $r . '
									with distinct(j) as dj,oh,o
									Match (dj)<-[:REPLACED_BY*0..1]-(:HPOterm)<-[:IS_A*0..20]-(ph:HPOterm)-[:PHENOTYPE_OF*0..1]->(link)-[r2:PHENOTYPE_OF]->(s:Subject)
									WHERE s.source_id = "' . $source_id . '" and s.uid = "' . $sourceUID . '" and (link:HPOterm or link:ORPHAterm) ';
				}
				// Query 10
				elseif ($r < 1 && $hpo == false)
				{
					$neo_query = 'Match (oo:ORPHAterm{orphaid:"' . $orpha_id . '"})<-[:IS_A*0..20]-(o:ORPHAterm)<-[ob:PHENOTYPE_OF]-(oh:HPOterm)-[:REPLACED_BY*0..1]->(:HPOterm)-[r:SIMILARITY]-(j:HPOterm)
									using index r:SIMILARITY(rel)
									where r.rel >= ' . $r . '
									with distinct(j) as dj,oh,o
									Match (dj)<-[:REPLACED_BY*0..1]-(:HPOterm)<-[:IS_A*0..20]-(ph:HPOterm)-[:PHENOTYPE_OF*0..1]->(s:ORPHAterm)-[r2:PHENOTYPE_OF]->(s:Subject)
									WHERE s.source_id = "' . $source_id . '" and s.uid = "' . $sourceUID . '" ';
				}
				$neo_query = $neo_query . 'return s.subjectid as subjectid,o.orphaid as orphaid, collect(oh.hpoid) as orphas_hpoterms, collect(ph.hpoid) as subjects_hpoterms order by s.subjectid';
			}
			// Second Call
			else
			{
				$neo_query = 'Match (oo:ORPHAterm{orphaid:"' . $orpha_id . '"})<-[:IS_A*0..20]-(o:ORPHAterm)<-[ob:PHENOTYPE_OF]-(oh:HPOterm) ';
				$neo_query = $neo_query . 'with o, collect(distinct(oh)) as coh, count(distinct(oh)) as ohn, ob.frequencycode as fc,collect(distinct([oh.hpoid,ob.frequencycode,oh.ICvsOMIM, oh.ICvsORPHA])) as hlink
                                    unwind coh as oh
                                    with  o,hlink,fc as FrequencyCode, sum(oh.ICvsOMIM) as OMIM_IC, sum(oh.ICvsORPHA) as ORPHA_IC, ohn
                                    with o, collect(hlink) as link, collect([FrequencyCode,OMIM_IC, ORPHA_IC]) as FrequencyCode, sum(OMIM_IC) as OMIM_IC, sum(ORPHA_IC) as ORPHA_IC, sum(ohn) as ohn
                                    unwind link as hlink
                                    unwind hlink as olink
                                    return o.orphaid as ORPHA, collect(distinct(olink)) as LINK, FrequencyCode, ORPHA_IC, OMIM_IC, ohn as MATCHN';
			}
		}
		return $neo_query;
	}

	private function CyphertoArray(CypherList $list): array
	{
		if (is_iterable($list) && get_class($list) == 'Laudis\Neo4j\Types\CypherList'){
			$list_array = $list->toArray();

			foreach ($list_array as & $item){
				if(is_iterable($item)){
					$item = $item->toArray();
				}
			}
		}

		return $list_array;
	}
}

