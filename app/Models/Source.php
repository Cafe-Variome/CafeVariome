<?php namespace App\Models;

/**
 * Name Source.php
 * @author Owen Lancaster
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 * 
 * Source model class. This class handles operations on source entities.
 */

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;

 class Source extends Model{

    protected $db;
    protected $table      = 'sources';
    protected $builder;

    protected $primaryKey = 'source_id';

    public function __construct(ConnectionInterface &$db){

        $this->db =& $db;
    }

    /**
     * 
     */
    public function getOnlineSources() {
        $this->builder = $this->db->table($this->table);
        $this->builder->where('status', 'online');
        $query = $this->builder->get()->getResultArray();
        $sources_options = array();
        foreach ($query as $source) {
            $sources_options[$source['name']] = $source['description'];
        }
        return $sources_options;
    }

    public function createSource($data) {
        $this->builder = $this->db->table($this->table);
		$this->builder->insert($data);
		$insert_id = $this->db->insertID();
		return $insert_id;
    }

    /**
     * 
     */
    public function updateSource($data) {
        $this->builder = $this->db->table($this->table);
        $this->builder->where('source_id', $data['source_id']);
        $this->builder->update($data);
    }


    /**
     * 
     */
    public function getSources() {
        $this->builder = $this->db->table($this->table);
        $query = $this->builder->get()->getResultArray();
        return $query;
    }


    /**
     * 
     */
    public function getSource($source_id) {

        $this->builder = $this->db->table($this->table);
        $query = $this->builder->where('source_id', $source_id);
        $query = $this->builder->get()->getResultArray();
        if($query)
        {
            return $query[0];
        }
        return null;
    }


    function getSpecificSources($source_ids){

        $this->builder = $this->db->table($this->table);
        $this->builder->whereIn('source_id',  $source_ids);
        $query = $this->builder->get()->getResultArray();

        return $query;
    }

    /**
     * 
     * 
     */
    function getSourceId($group_id) {
        $this->builder = $this->db->table('network_groups_sources');
        $this->builder->select('source_id');
        $this->builder->where('group_id', $group_id);

        $sources = $this->builder->get()->getResultArray();

		return $sources ? $sources[0] : null;
	}

    public function getSourceIDByName($source) {
        $this->builder = $this->db->table($this->table);
        $this->builder->select('source_id');
        $this->builder->where('name', $source);
        $query = $this->builder->get()->getResultArray();
        return $query? $query[0]['source_id'] : null;
    }

    /**
     * Is Source Locked - Is the given Source Locked?
     *
     * @param int $source_id - The source we are checking
     * @return int 0 if not locked | 1 if it is
     */
    public function isSourceLocked($source_id): bool {
        $this->builder = $this->db->table($this->table);

        $this->builder->select('elastic_lock');
        $this->builder->where('source_id', $source_id);
        $query = $this->builder->get()->getResultArray();
        return $query ? $query[0]['elastic_lock'] : false;
    }
    /**
     * 
     */
    function countSourceEntries() {
        $this->builder = $this->db->table('eavs');
        $this->builder->select('COUNT(distinct(subject_id)) as total, source');
        $this->builder->groupBy('source');
        $query = $this->builder->get()->getResultArray();
        $source_counts = array();
        foreach ($query as $r) {
            $source_counts[$r->source] = $r->total;
        }
        return $source_counts;
    }

    /**
     * 
     */
    public function deleteVariantsPhenotypes($source_id) {
        $this->builder = $this->db->table('eavs');
        $this->builder->delete(['source' => $source_id]);
    }

    /**
     * 
     */
    public function deleteSource($source_id) {
        $this->builder = $this->db->table($this->table);
        $this->builder->delete(['source_id' => $source_id]);
    }

    /**
     * Get Status For Source - Get all rows from UploadDataStatus for given source
     *
     * @param int $source_id  - The source id we are wanting rows from
     * @return array $query   - Our Results
     */
    public function getSourceStatus($source_id) {
        // SELECT UploadDataStatus.FileName, UploadDataStatus.uploadStart, UploadDataStatus.uploadStart, UploadDataStatus.Status, UploadDataStatus.elasticStatus, users.email FROM UploadDataStatus INNER JOIN users ON UploadDataStatus.user_id=users.id;
        
        $this->builder = $this->db->table('uploaddatastatus');
        $this->builder->select('UploadDataStatus.ID,UploadDataStatus.FileName,UploadDataStatus.uploadEnd,UploadDataStatus.uploadStart,UploadDataStatus.Status,UploadDataStatus.elasticStatus,UploadDataStatus.patient,UploadDataStatus.tissue,users.email,sources.name');
        $this->builder->join('users', 'UploadDataStatus.user_id=users.id', 'inner');
        $this->builder->join('sources', 'UploadDataStatus.source_id=sources.source_id', 'inner');
        if ($source_id != 'all') {
            $this->builder->where('UploadDataStatus.source_id', $source_id);
        }
        $query = $this->builder->get()->getResultArray();
        return $query;
    }

    public function canCurateSource($source_id, $user_id) {
        $this->builder = $this->db->table('curators');

        $where = "source_id = '$source_id' AND user_id = '$user_id'";
        $this->builder->where($where);
        $query = $this->builder->get()->getResultArray();
        $count = $this->builder->countAllResults();
        return $count;
    }

 }