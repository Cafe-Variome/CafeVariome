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
        $this->builder->select('id,attribute,value');
        $this->builder->where('source_id', $source_id);
        $this->builder->where('id>', $offset);

        $this->builder->limit($limit);
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

    /**
     * Json Insert - Taking the data from recursivePacket we add to our MySQL transaction
     * Adding to eavs table
     *
     * Moved to upload model by Mehdi Mehtarizadeh (02/08/2019)
     * @param string $uid   - The md5 ID linking groups together
     * @param int $source   - The ID of the source we are linking this data to - sources
     * @param int $file     - The ID of the file we have generated this data from - UploadDataStatus
     * @param string $id    - The subject id of the current phenoPacket
     * @param string $key   - The attribute column for given datapoint
     * @param string $value - The value column for given datapoint
     *
     * @return void
     */
    public function createEAV(string $uid, int $source, int $file, string $id, string $key, string $value)
    {
        $malicious_chars = ['\\', chr(39), chr(34), '/', '’', '<', '>', '&', ';'];
        $key = str_replace($malicious_chars, '', $key);
        $value = str_replace($malicious_chars, '', $value);

        $this->builder = $this->db->table($this->table);

        $data = [
            'uid'        =>  $uid,
            'source_id'     => $source,
            'file_id'   => $file,
            'subject_id' => $id,
            'attribute'  => $key,
            'value'      => $value
        ];

        $this->builder->insert($data);
    }

    public function updateEAVs(array $data, array $conds = null)
    {        $this->builder = $this->db->table($this->table);
        if ($conds) {
            $this->builder->where($conds);
        }
        $this->builder->update($data);
    }

    /*
     * @deprecated
     */
    public function getHPOTermsWithNegatedBySourceId(int $source_id, array $hpo_attribute_names = [], array $negated_hpo_attribute_names = [])
    {
        $subjectHPOWithNegatedArray = [];

        $uidSubjectIds = $this->getEAVs('uid, subject_id', ['source_id' => $source_id], true);
        $uids = [];
        foreach ($uidSubjectIds as $uid_sid) {
            $uids[$uid_sid['uid']] = $uid_sid['subject_id'];
        }

        $uidHPOTerms = $this->getHPOTermsBySourceId($source_id, $hpo_attribute_names);

        $uidHPOArray = [];
        foreach ($uidHPOTerms as $hpo) {
            $uidHPOArray[$hpo['uid']] = $hpo['value'];
        }

        $negatedHPOTerms = $this->getNegatedHPOTermsBySourceId($source_id, $negated_hpo_attribute_names);

        $uidNegatedHPOArray = [];
        foreach ($negatedHPOTerms as $negated_hpo) {
            $uidNegatedHPOArray[$negated_hpo['uid']] = $negated_hpo['value'];
        }

        foreach ($uids as $uid => $subject_id) {
            if (array_key_exists($uid, $uidHPOArray)) {
                if (array_key_exists($uid, $uidNegatedHPOArray)) {
                    $subjectHPOWithNegatedArray[$subject_id][] = ['hpo' => $uidHPOArray[$uid], 'negated' => $uidNegatedHPOArray[$uid]];
                }
                else {
                    $subjectHPOWithNegatedArray[$subject_id][] = ['hpo' => $uidHPOArray[$uid], 'negated' => null];
                }
            }

        }

        return $subjectHPOWithNegatedArray;
    }

    public function getORPHATermsBySourceId(int $source_id, array $orpha_attribute_names = [], int $limit = -1, int $offset = -1)
    {
        $this->builder = $this->db->table($this->table);
        $this->builder->select('id, subject_id, value');
		$this->builder->where('source_id', $source_id);

		if ($offset > 0){
			$this->builder->where('id>', $offset);
		}

        if (count($orpha_attribute_names) > 0) {
            $this->builder->whereIn('attribute', $orpha_attribute_names);
        }
        else {
            $this->builder->like('value', "orpha:", 'after');
			$this->builder->orLike('value', "ordo:", 'after');
		}

		if ($limit > 0) {
			$this->builder->limit($limit);
		}

		$query = $this->builder->get()->getResultArray();

		//Loop over results to make sure terms start with the correct prefix.
		for($c = 0; $c < count($query); $c++)
		{
			if (is_numeric($query[$c]['value'])){
				$query[$c]['value'] = 'ORPHA:' . $query[$c]['value'];
			}
			else if(stripos($query[$c]['value'], 'ordo:')){
				$query[$c]['value'] = str_ireplace('ordo:', 'ORPHA:', $query[$c]['value']);
			}
		}

        return $query;
    }

    public function getHPOTermsBySourceId(int $source_id, array $hpo_attribute_names = [], int $limit = -1, int $offset = -1)
    {
        $this->builder = $this->db->table($this->table);
        $this->builder->select('id, subject_id, value');
        $this->builder->where('source_id', $source_id);
        $this->builder->where('attribute !=', 'ancestor_hpo_id'); // attribute != "ancestor_hpo_id"
        $this->builder->where('attribute !=', 'classOfOnset_id'); // attribute != 'classOfOnset_id'

		if ($offset > 0){
			$this->builder->where('id>', $offset);
		}
        if (count($hpo_attribute_names) > 0) {
            $this->builder->whereIn('attribute', $hpo_attribute_names);
        }
        else {
            $this->builder->like('value', 'hp:', 'after');
        }

        if ($limit > 0) {
			$this->builder->limit($limit);
		}

        $data = $this->builder->get()->getResultArray();

		//Loop over results to make sure terms start with the correct prefix.
		for($c = 0; $c < count($data); $c++)
		{
			if (is_numeric($data[$c]['value'])){
				$data[$c]['value'] = 'HP:' . $data[$c]['value'];
			}
		}

		return $data;
    }

    public function getNegatedHPOTermsBySourceId(int $source_id, array $negated_hpo_attribute_names = [], int $limit = -1, int $offset = -1)
    {
        $this->builder = $this->db->table($this->table);
		$this->builder->select('id, subject_id, value');
        $this->builder->where('source_id', $source_id);

		if ($offset > 0){
			$this->builder->where('id>', $offset);
		}
        if (count($negated_hpo_attribute_names) > 0) {
            $this->builder->whereIn('attribute', $negated_hpo_attribute_names);
        }
        else {
            $this->builder->where('attribute', 'negated');
        }

		if ($limit > 0) {
			$this->builder->limit($limit);
		}

        $data = $this->builder->get()->getResultArray();

		//Loop over results to make sure terms start with the correct prefix.
		for($c = 0; $c < count($data); $c++)
		{
			if (is_numeric($data[$c]['value'])){
				$data[$c]['value'] = 'HP:' . $data[$c]['value'];
			}
		}

        return $data;
    }

    public function getHPOTermsForSources(array $source_ids, array $hpo_attribute_names = [])
    {
        $this->builder = $this->db->table($this->table);
        $this->builder->select('value');
        $this->builder->distinct();
        $this->builder->whereIn('source_id', $source_ids);

        if (count($hpo_attribute_names) > 0) {
            $this->builder->whereIn('attribute', $hpo_attribute_names);
        }
        else {
            $this->builder->like('value', 'hp:', 'after');
        }

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
        $this->builder->where("file_id",$file_id);
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
        $this->builder->where('file_id', $file_id);
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

    public function setElasticFlag(int $source_id)
    {
        $this->builder = $this->db->table($this->table);
        $data = ['elastic' => 1];

        $this->builder->where('source_id', $source_id);
        $this->builder->update($data);
    }

    /**
     * countUnaddedEAVs
     * For a given source check whether there is any data in MySQL which isnt in ElasticSearch
     *
     * @param int $source_id  - The name of the source
     * @return int $noOfRecords    - Count of how many records there are which arent in ElasticSearch
     */
    public function countUnaddedEAVs(int $source_id):int
    {
        $this->builder = $this->db->table($this->table);

        $this->builder->where('elastic', 0);
        $this->builder->where('source_id', $source_id);

        $count = 0;
        $count = $this->builder->countAllResults();

        return $count;
    }

    /**
     * Check Negated for HPO - For given list of HPO terms check if the group they belong to has a
     * negated = false flag. Then return only those
     *
     * @param array $hpo    - List of HPO terms to check
     * @param int $file_id  - the file id where these HPO terms have come from
     * @return array $final - List of HPO terms which have negated=0
     */
    public function checkNegatedForHPO(array $hpos, int $file_id, string $hpo_attribute_name): array
	{
        $this->builder = $this->db->table($this->table);

        $final =[];
        foreach ($hpos as $hpo) {
        	$this->builder->select('value');
            $this->builder->where('value', $hpo);
            $this->builder->where('attribute', $hpo_attribute_name);
            $query = $this->builder->get()->getResultArray();

            if (count($query) == 1) {
                array_push($final, $hpo);
            }
        }

        return $final;
    }

    public function countUniqueUIDs(int $source_id): int
    {
        $this->builder = $this->db->table($this->table);

        $this->builder->select('uid, subject_id');
        $this->builder->where('elastic', 0);
        $this->builder->where('source_id', $source_id);
        $this->builder->distinct();

        $count = 0;
        $count = $this->builder->countAllResults();

        return $count;
    }

	public function countUnaddedRecordsByAttributeNames(int $source_id, array $attribute_names): int
	{
		$this->builder = $this->db->table($this->table);

		$this->builder->whereIn('attribute', $attribute_names);
		$this->builder->where('source_id', $source_id);

		$count = 0;
		$count = $this->builder->countAllResults();

		return $count;
    }

	public function getLastIdByUID(string $uid): int
	{
		$this->builder = $this->db->table($this->table);

		$this->builder->select('id');
		$this->builder->where('uid', $uid);
		$this->builder->orderBy('id', 'DESC');
		$this->builder->limit(1);

		$query = $this->builder->get()->getResultArray();
		if (count($query) == 1){
			return $query[0]['id'];
		}

		return -1;
	}
}
