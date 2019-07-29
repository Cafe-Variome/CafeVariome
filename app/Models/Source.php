<?php namespace App\Models;

/**
 * Name Source.php
 * @author Owen Lancaster
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
    public function getSources() {
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
    public function getSourcesFull() {
        $this->builder = $this->db->table($this->table);
        $query = $this->builder->get()->getResultArray();
        return $query;
    }

    /**
     * 
     */
    public function getSourceSingleFull($source_id) {

        $this->builder = $this->db->table($this->table);
        $query = $this->builder->where('source_id', $source_id);
        $query = $this->builder->get()->getResultArray();
        if($query)
        {
            return $query[0];
        }
        return null;
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
        
        $this->builder = $this->db->table('UploadDataStatus');
        $this->builder->select('UploadDataStatus.ID,UploadDataStatus.FileName,UploadDataStatus.uploadEnd,UploadDataStatus.uploadStart,UploadDataStatus.Status,UploadDataStatus.elasticStatus,UploadDataStatus.patient,UploadDataStatus.tissue,users.email,sources.name');
        $this->builder->join('users', 'UploadDataStatus.user_id=users.id', 'inner');
        $this->builder->join('sources', 'UploadDataStatus.source_id=sources.source_id', 'inner');
        if ($source_id != 'all') {
            $this->builder->where('UploadDataStatus.source_id', $source_id);
        }
        $query = $this->builder->get()->getResultArray();
        return $query;
    }

 }