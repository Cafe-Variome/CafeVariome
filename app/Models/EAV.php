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

    public function getHPOTerms(int $source_id) 
    {
        $this->builder = $this->db->table('eavs e');

        $this->builder->select('e.subject_id as subject, e.value as hpo,m.value as negated');
        $this->builder->join('eavs m', 'e.uid = m.uid', 'inner');
        $this->builder->like('e.value', 'HP', 'after');
        //$this->builder->where('m.attribute', "negated");
        $this->builder->orWhere('m.attribute', "negated");
        $this->builder->where('e.elastic', 0);
        $this->builder->where('e.source_id', $source_id);

        $data = $this->builder->get()->getResultArray();

        foreach ($data as &$drow) {
            if ($drow['hpo'] == $drow['negated']) {
                $drow['negated'] = null;
            }
        }

        list($subject,$new_data,$t) = array("",[],0);
        
        for ($i=0; $i < count($data); $i++) { 
            if ($subject != $data[$i]['subject']) {
                $subject = $data[$i]['subject'];
                $new_data[$subject] = [];
                $t = 0;
            }
            $new_data[$subject][$t]['hpo'] = $data[$i]['hpo'];
            $new_data[$subject][$t]['negated'] = $data[$i]['negated'];
            $t++;
        }

        return $new_data;
    }

    public function getORPHATerms(int $source_id)
    {
        $this->builder = $this->db->table('eavs');
        $this->builder->select('subject_id, attribute, value');
        $this->builder->where('attribute', "Phenotype_ORPHA"); //Diagnosis
        $this->builder->where('source_id', $source_id);
        //$this->builder->where('elastic', 0);

        $query = $this->builder->get()->getResultArray();
        $data = [];
        foreach ($query as $record) {
            $orpha_terms = [];
            if (array_key_exists($record['subject_id'], $data)) {
                $data[$record['subject_id']][] = ['orpha' => $record['value']];
            }
            else {
                $data[$record['subject_id']] = [['orpha' => $record['value']]];
            }
        }

        return $data;
    }
}