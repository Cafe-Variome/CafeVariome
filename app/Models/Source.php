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

    public function createSource($source_data) {
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
        $this->builder->update('sources', $data);
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
        return $query;
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
 }