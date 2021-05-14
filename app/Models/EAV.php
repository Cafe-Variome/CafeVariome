<?php namespace App\Models;

/**
 * EAV.php 
 * 
 * Created: 09/08/2019
 * 
 * @author Mehdi Mehtarizadeh
 * 
 * This class handles data operations for Entity Attribute Values in EAVs table as well as other dependant tables.
 */

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;

class EAV extends Model{

	protected $db;
    protected $table      = 'eavs';
    protected $builder;

    protected $primaryKey = 'id';

    public function __construct(ConnectionInterface &$db = null)
    {
        if ($db != null) {
            $this->db =& $db;
        }
        else {
            $this->db = \Config\Database::connect();
        }
    }

    public function getEAVsForSource(int $source_id, int $limit, int $offset){
        $this->builder = $this->db->table($this->table);
        $this->builder->select('attribute,value');
        $this->builder->where('source_id', $source_id);
        $this->builder->limit($limit, $offset);
        $query = $this->builder->get()->getResultArray();

        return $query;
    }

    public function getEAVs($cols, array $conds = null, bool $isDistinct = false, int $limit = -1, int $offset = -1)
    {
        $this->builder = $this->db->table($this->table);
        if ($cols) {
            $this->builder->select($cols);
        }
        if ($conds) {
            $this->builder->where($conds);
        }
        if ($isDistinct) {
            $this->builder->distinct();
        }
        if ($limit > 0) {
            if ($offset > 0) {
                $this->builder->limit($limit, $offset);
            }
            $this->builder->limit($limit);
        }

        $query = $this->builder->get()->getResultArray();
        return $query; 
    }

    public function updateEAVs(array $data, array $conds = null)
    {
        $this->builder = $this->db->table($this->table);
        if ($conds) {
            $this->builder->where($conds);
        }
        $this->builder->update($data);
    }

    public function getHPOTermsWithNegatedBySourceId(int $source_id) 
    {
        $subjectHPOWithNegatedArray = [];

        $uidSubjectIds = $this->getEAVs('uid, subject_id', ['source_id' => $source_id], true);
        $uids = [];
        foreach ($uidSubjectIds as $uid_sid) {
            $uids[$uid_sid['uid']] = $uid_sid['subject_id'];
        }

        $uidHPOTerms = $this->getHPOTermsBySourceId($source_id);

        $uidHPOArray = [];
        foreach ($uidHPOTerms as $hpo) {
            $uidHPOArray[$hpo['uid']] = $hpo['value'];
        }

        $negatedHPOTerms = $this->getNegatedHPOTermsBySourceId($source_id);

        $uidNegatedHPOArray = [];
        foreach ($negatedHPOTerms as $negated_hpo) {
            $uidNegatedHPOArray[$negated_hpo['uid']] = $negated_hpo['value'];
        }

        foreach ($uids as $uid => $subject_id) {
            if (array_key_exists($uid, $uidHPOArray)) {
                if (array_key_exists($uid, $negatedHPOTerms)) {
                    $subjectHPOWithNegatedArray[$subject_id][] = ['hpo' => $uidHPOArray[$uid], 'negated' => $uidNegatedHPOArray[$uid]];
                }
                else {
                    $subjectHPOWithNegatedArray[$subject_id][] = ['hpo' => $uidHPOArray[$uid], 'negated' => 0];
                }
            }

        }

        return $subjectHPOWithNegatedArray;
    }

    public function getORPHATerms(int $source_id)
    {
        $this->builder = $this->db->table($this->table);
        $this->builder->select('subject_id, attribute, value');
        $this->builder->where('attribute', "Phenotype_ORPHA"); //Diagnosis
        $this->builder->where('source_id', $source_id);

        $query = $this->builder->get()->getResultArray();
        $data = [];
        foreach ($query as $record) {
            if (array_key_exists($record['subject_id'], $data)) {
                $data[$record['subject_id']][] = ['orpha' => $record['value']];
            }
            else {
                $data[$record['subject_id']] = [['orpha' => $record['value']]];
            }
        }

        return $data;
    }

    public function getHPOTermsBySourceId(int $source_id)
    {
        $this->builder = $this->db->table($this->table);
        $this->builder->select('uid, value');
        $this->builder->where('source_id', $source_id);
        $this->builder->where('attribute !=', 'ancestor_hpo_id'); // attribute != "ancestor_hpo_id"
        $this->builder->where('attribute !=', 'classOfOnset_id'); // attribute != 'classOfOnset_id'
        $this->builder->like('value', 'hp:', 'after');

        $data = $this->builder->get()->getResultArray();

        return $data;
    }

    public function getNegatedHPOTermsBySourceId(int $source_id)
    {
        $this->builder = $this->db->table($this->table);
        $this->builder->select('uid, value');
        $this->builder->where('source_id', $source_id);
        $this->builder->where('attribute', 'negated');

        $data = $this->builder->get()->getResultArray();

        return $data;
    }

    public function getHPOTermsForSources(array $source_ids)
    { 
        $this->builder = $this->db->table($this->table);
        $this->builder->select('value');
        $this->builder->distinct();
        $this->builder->whereIn('source_id', $source_ids);
        $this->builder->like('value', 'HP:', 'after');

        $terms = $this->builder->get()->getResultArray();

        $hpo_terms = [];
		foreach ($terms as $term) {
			$hpo_terms[] = $term['value'];
		}
		return $hpo_terms;
	}

    public function getUniqueAttributesAndValuesByFileIdAndSourceId(int $file_id, int $source_id)
    {
        $this->builder = $this->db->table($this->table);

        $this->builder->select("attribute, value, count(*) AS count");
        $this->builder->where("source_id", $source_id);
        $this->builder->where("fileName",$file_id);
        $this->builder->groupBy(["attribute","value"]);

        $query = $this->builder->get()->getResultArray();

        $data = [];
        $attributeValueArray = [];

        foreach ($query as $row) {
            $data[] = $row;
        }

        $currAtt = "";
        for ($i=0; $i < count($data); $i++) { 
            if ($data[$i]["attribute"] != $currAtt){
                $currAtt = $data[$i]["attribute"];
                $attributeValueArray[$data[$i]["attribute"]] = array();
                $attributeValueArray[$data[$i]["attribute"]][$data[$i]['value']] = $data[$i]['count'];
            }
            else {
                $attributeValueArray[$data[$i]["attribute"]][$data[$i]['value']] = $data[$i]['count'];
            }
        }

        return $attributeValueArray;
    }

    public function deleteRecordsBySourceId(int $source_id)
    {
        $this->builder = $this->db->table($this->table);
        $this->builder->where('source_id', $source_id);
        $this->builder->delete();
    }

    public function deleteRecordsByFileId(int $file_id)
    {
        $this->builder = $this->db->table($this->table);
        $this->builder->where('fileName', $file_id);
        $this->builder->delete();
    }

    /**
     * resetElasticFlag
     * Set Elastic boolean to false for all data in a given source
     *
     * @param int $source_id  - The id of the source
     * @return N/A
     */
    function resetElasticFlag(int $source_id) {
        $this->builder = $this->db->table($this->table);
        $data = ['elastic' => 0];

        $this->builder->where('source_id', $source_id);
        $this->builder->update($data);
    }

}