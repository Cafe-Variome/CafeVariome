<?php namespace App\Libraries\CafeVariome\Query;


use App\Models\Settings;
use App\Libraries\CafeVariome\Entities\Source;
/**
 * ReactomeQuery.php
 * Created 12/08/2021
 *
 * @author Colin Veal
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 *
 */

class ReactomeQuery extends AbstractQuery
{

	public function __construct()
	{

	}

	public function Execute(array $clause, Source $source)
	{
		$source_id = $source->getID();

		$reactome_id = $clause['reactome_id'];
		$protein_effect = array_key_exists('protein_effect', $clause) ? $clause['protein_effect'] : [];
		$protein_effect_string = $this->concatenateArray($protein_effect, ' or ');
		$allele_frequency = array_key_exists('af', $clause) ? $clause['af'] : null;

		$af_clause = '';
		if($allele_frequency != null)
		{
				$af_clause .= "and (r.af < $allele_frequency)";
		}

		$neo_query = "MATCH (n:Pathway)<-[:PART_OF_PATHWAY*0..1]-(m:Reaction)-[:USING_GENE_PRODUCT*0..1]->(g:Gene)-[:HAS_EFFECT]->(p:Protein_Effect) WHERE (n.ReactomePathwayID = '$reactome_id')";
		
		if($protein_effect_string != "")
		{
			$neo_query .= " and ($protein_effect_string)";
		}

		$neo_query .= " with p MATCH (p)-[r:HAS_VARIANT]->(s:Subject) WHERE (s.source_id = '$source_id') $af_clause RETURN distinct(s.subjectid) as subjectid;";

		$pat_ids = [];

		$records = $this->getNeo4JInstance()->runQuery($neo_query);

		foreach ($records as $record) 
		{
			$pat_ids[] = $record->get('subjectid');
		}
		
		return $pat_ids;
	}

	private function concatenateArray(array $arr, string $delimiter)
	{	
		if (count($arr) > 0)
		{
			return implode($delimiter, $arr);
		}
		return "";
	}

}
