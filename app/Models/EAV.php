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
	 * createEAV
     * @param string $uid   - The md5 ID linking groups together
     * @param int $source_id   - The ID of the source we are linking this data to - sources
     * @param int $file     - The ID of the file we have generated this data from - UploadDataStatus
     * @param string $id    - The subject id of the current phenoPacket
     * @param string $key   - The attribute column for given datapoint
     * @param string $value - The value column for given datapoint
     *
     * @return void
     */
    public function createEAV(string $uid, int $source_id, int $file, string $id, string $key, string $value)
    {
        $malicious_chars = ['\\', chr(39), chr(34), '/', '’', '<', '>', '&', ';'];
        $key = str_replace($malicious_chars, '', $key);
        $value = str_replace($malicious_chars, '', $value);

        $data = [
            'uid'        =>  $uid,
            'source_id'     => $source_id,
            'file_id'   => $file,
            'subject_id' => $id,
            'attribute_id'  => $key,
            'value_id'      => $value
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

	public function getEAVsByFileIdAndAttributeIds(int $file_id, array $attribute_ids, int $limit, int $offset, bool $unindexedOnly = true)
	{
		$this->builder->select('id, uid, subject_id, attribute_id, value_id');
		$this->builder->whereIn('attribute_id', $attribute_ids);
		$this->builder->where('file_id', $file_id);
		if($unindexedOnly){
			$this->builder->where('indexed', false);
		}
		$this->builder->where('id>', $offset);
		$this->builder->limit($limit);

		return $this->builder->get()->getResultArray();
	}

	public function getUniqueUIDsAndSubjectIdsByFileIdAndAttributeIds(int $file_id, array $attribute_ids, int $limit, int $offset, bool $unindexedOnly = true)
	{
		$this->builder->select('uid, subject_id');
		$this->builder->distinct();
		$this->builder->where('file_id', $file_id);
		$this->builder->whereIn('attribute_id', $attribute_ids);
		if($unindexedOnly){
			$this->builder->where('indexed', false);
		}
		$this->builder->where('id>', $offset);
		$this->builder->limit($limit);

		return $this->builder->get()->getResultArray();
	}

	public function countEAVsByFileIdAndAttributeIds(int $file_id, array $attribute_ids, bool $unindexedOnly = true): int
	{
		$this->builder->select('id');
		$this->builder->where('file_id', $file_id);
		$this->builder->whereIn('attribute_id', $attribute_ids);
		if($unindexedOnly){
			$this->builder->where('indexed', false);
		}

		return $this->builder->countAllResults();
	}

	public function countUniqueUIDsByFileIdAndAttributeIds(int $file_id, array $attribute_ids, bool $unindexedOnly = true): int
	{
		$this->builder->select('uid, subject_id');
		$this->builder->where('file_id', $file_id);
		$this->builder->whereIn('attribute_id', $attribute_ids);
		if($unindexedOnly){
			$this->builder->where('indexed', false);
		}
		$this->builder->distinct();

		return $this->builder->countAllResults();
	}

	public function countUniqueUIDsBySourceIdAndAttributeIds(int $source_id, array $attribute_ids, bool $unindexedOnly = true): int
	{
		$this->builder->select('uid, subject_id');
		$this->builder->where('source_id', $source_id);
		$this->builder->whereIn('attribute_id', $attribute_ids);

		if($unindexedOnly){
			$this->builder->where('indexed', false);
		}
		$this->builder->distinct();

		return $this->builder->countAllResults();
	}

	public function countUniqueSubjectIdsBySourceIdAndAttributeIds(int $source_id, array $attribute_ids, bool $unindexedOnly = true): int
	{
		$this->builder->select('subject_id');
		$this->builder->where('source_id', $source_id);
		$this->builder->whereIn('attribute_id', $attribute_ids);

		if($unindexedOnly){
			$this->builder->where('indexed', false);
		}
		$this->builder->distinct();

		return $this->builder->countAllResults();
	}

	public function countEAVsBySourceIdAndAttributeIds(int $source_id, array $attribute_ids, bool $unindexedOnly = true): int
	{
		$this->builder->select('id');
		$this->builder->where('source_id', $source_id);
		$this->builder->whereIn('attribute_id', $attribute_ids);
		if($unindexedOnly){
			$this->builder->where('indexed', false);
		}

		return $this->builder->countAllResults();
	}

	public function getUniqueUIDsAndSubjectIdsBySourceIdAndAttributeIds(int $source_id, array $attribute_ids, int $limit, int $offset, bool $unindexedOnly = true)
	{
		$this->builder->select('uid, subject_id, file_id');
		$this->builder->distinct();
		$this->builder->where('source_id', $source_id);
		$this->builder->whereIn('attribute_id', $attribute_ids);
		if($unindexedOnly){
			$this->builder->where('indexed', false);
		}
		$this->builder->where('id>', $offset);
		$this->builder->limit($limit);

		return $this->builder->get()->getResultArray();
	}

	public function getEAVsBySourceIdAndAttributeIds(int $source_id, array $attribute_ids, int $limit, int $offset, bool $unindexedOnly = true)
	{
		$this->builder->select('id, uid, subject_id, file_id, attribute_id, value_id');
		$this->builder->whereIn('attribute_id', $attribute_ids);
		$this->builder->where('source_id', $source_id);
		if($unindexedOnly){
			$this->builder->where('indexed', false);
		}
		$this->builder->where('id>', $offset);
		$this->builder->limit($limit);

		return $this->builder->get()->getResultArray();
	}

	public function getUniqueSubjectIdsBySourceIdAndAttributeIds(int $source_id, array $attribute_ids, int $limit, int $offset, bool $unindexedOnly = true)
	{
		$this->builder->select('subject_id, file_id');
		$this->builder->distinct();
		$this->builder->where('source_id', $source_id);
		$this->builder->whereIn('attribute_id', $attribute_ids);
		if($unindexedOnly){
			$this->builder->where('indexed', false);
		}
		$this->builder->where('id>', $offset);
		$this->builder->limit($limit);

		return $this->builder->get()->getResultArray();
	}

    public function deleteRecordsBySourceId(int $source_id)
    {
        $this->builder->where('source_id', $source_id);
        $this->builder->delete();
    }

    public function deleteRecordsByFileId(int $file_id)
    {
        $this->builder->where('file_id', $file_id);
        $this->builder->delete();
    }

	public function setIndexedFlagBySourceIdAndAttributeIds(int $source_id, array $attribute_ids)
	{
		$data = ['indexed' => 1];
		$this->builder->where('source_id', $source_id);
		$this->builder->whereIn('attribute_id', $attribute_ids);
		$this->builder->update($data);
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
