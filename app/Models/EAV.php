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

    public function __construct(ConnectionInterface &$db){

        $this->db =& $db;
    }

    public function getEAVsForSource(int $source_id){
        $this->builder = $this->db->table($this->table);
        $this->builder->select('attribute,value');
        $this->builder->where('source', $source_id);
        $query = $this->builder->get()->getResultArray();

        return $query;
    }

    public function getEAVs($cols, array $conds = null, bool $isDistinct = false, int $limit = -1, int $offset = -1){
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

    public function updateEAVs(array $data, array $conds = null){
        $this->builder = $this->db->table($this->table);
        if ($conds) {
            $this->builder->where($conds);
        }
        $this->builder->update($data);
    }

    public function retrieveUpdateNeo4j($source_id) {

        $this->builder = $this->db->table('eavs e');

        $this->builder->select('e.subject_id as subject, e.value as hpo,m.value as negated');
        $this->builder->join('eavs m', 'e.uid = m.uid', 'inner');
        $this->builder->like('e.value', 'HP', 'after');
        //$this->builder->where('m.attribute', "negated");
        $this->builder->orWhere('m.attribute', "negated");
        $this->builder->where('e.elastic', 0);
        $this->builder->where('e.source', $source_id);

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
        $neo4jModel = new Neo4j($this->db);

        $sourceModel = new Source($this->db);
        $source_name = $sourceModel->getSourceNameByID($source_id);

        $neo4jModel->toUpdate($new_data, $source_name);
    }
}