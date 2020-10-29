<?php namespace App\Models;

/**
 * Name: Page.php
 * 
 * Created: 19/02/2020
 * 
 * @author Mehdi Mehtarizadeh
 * 
 */

use CodeIgniter\Model;

class Page extends Model 
{
    protected $db;
    protected $table      = 'pages';
    protected $builder;

    protected $primaryKey = 'id';

	public function __construct(ConnectionInterface &$db = null){
        if ($db != null) {
            $this->db =& $db;
        }
        else {
            $this->db = \Config\Database::connect();
        }
		$this->builder = $this->db->table($this->table);
    }
    
    function getPages(string $cols = null, array $conds = null, array $groupby = null, bool $isDistinct = false, int $limit = -1, int $offset = -1){
		if ($cols) {
            $this->builder->select($cols);
        }
        if ($conds) {
            $this->builder->where($conds);
        }
        if ($groupby) {
            $this->builder->groupBy($groupby);
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
    
    public function createPage(array $data)
    {
        $this->builder->insert($data);
    }

    public function updatePage(array $data, array $conds) {
        if ($conds) {
            $this->builder->where($conds);
        }
        $this->builder->update($data);
    }

    public function deletePage(int $page_id)
    {
        $this->builder->where('id', $page_id);
        $this->builder->delete();
    }
}
