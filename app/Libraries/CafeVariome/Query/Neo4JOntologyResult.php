<?php namespace App\Libraries\CafeVariome\Query;

use App\Models\Attribute;
use App\Models\Ontology;
use App\Models\Source;

/**
 * Neo4JResult.php
 * Created 27/10/2021
 *
 * @author Mehdi Mehtarizadeh
 *
 */

class Neo4JOntologyResult extends AbstractResult
{

    public function extract(array $ids, string $attribute, int $source_id): array
    {
		$neo4jClient = $this->getNeo4JInstance();
		$attributeModel = new Attribute();
		$ontologyModel = new Ontology();
		$sourceModel = new Source();

		$sourceUID = $sourceModel->getSourceUID($source_id);
		$attributeId = $attributeModel->getAttributeIdByNameAndSourceId($attribute, $source_id);
		$ontologyAssociations = $attributeModel->getOntologyAssociationsByAttributeId($attributeId);

		$idsString = '';
		for($c = 0; $c < count($ids); $c++)
		{
			if ($c != count($ids) - 1){
				$idsString .= "'$ids[$c]', ";
			}
			else{
				$idsString .= "'$ids[$c]'";
			}
		}

		$results = [];

		foreach ($ontologyAssociations as $ontologyAssociation) {
			$ontologyId = $ontologyAssociation['ontology_id'];
			$ontology = $ontologyModel->getOntology($ontologyId);
			$ontologyNodeKey = $ontology['node_key'];
			$ontologyNodeType = $ontology['node_type'];
			$ontologyTermName = $ontology['term_name'];

			$neo4jQuery = "MATCH (s:Subject)-[]-(n:$ontologyNodeType) where (s.subjectid IN [$idsString] AND s.source_id='$source_id' AND s.uid='$sourceUID') RETURN distinct s.subjectid as subjectid, n.$ontologyNodeKey as node_key";

			if ($ontologyTermName != null && $ontologyTermName != ''){
				$neo4jQuery .= ",n.$ontologyTermName as term_name";
			}

			$records = $neo4jClient->runQuery($neo4jQuery);

			foreach ($records as $record) {
				$subjectId = $record->get('subjectid');
				$nodeKey = $record->get('node_key');

				$termName = '';
				if ($ontologyTermName != null && $ontologyTermName != '') {
					$termName = $record->get('term_name');
				}

				if (!array_key_exists($subjectId, $results)){
					$results[$subjectId] = [$nodeKey . ' (' . $termName .')'];
				}
				else{
					array_push($results[$subjectId], $nodeKey . ' (' . $termName .')');
				}
			}
		}

		return $results;
    }
}
