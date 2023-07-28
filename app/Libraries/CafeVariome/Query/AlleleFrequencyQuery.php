<?php namespace App\Libraries\CafeVariome\Query;


use App\Models\Settings;
use App\Libraries\CafeVariome\Entities\Source;

/**
 * AlleleFrequencyQuery.php
 * Created 03/02/2022
 *
 * @author Colin Veal
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 *
 */

class AlleleFrequencyQuery extends AbstractQuery
{

	public function __construct()
	{

	}

	public function execute(array $clause, Source $source)
	{
		$source_id = $source->getID();

		$allele_frequency = $clause['af'];
		$neo_query = "MATCH (n:Subject)<-[r:HAS_VARIANT]-(m:Protein_Effect) WHERE r.af < '$allele_frequency' and n.source_id = '$source_id' RETURN n as subjectid";

		$pat_ids = [];

		$records = $this->getNeo4JInstance()->runQuery($neo_query);
		$recCount = count($records);
		for ($i = 0; $i < $recCount; $i++) 
		{
			$pat_ids[] = $records[$i]->get('subjectid');
			unset($records[$i]);
		}
		
		return $pat_ids;
	}

}
