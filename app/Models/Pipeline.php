<?php namespace App\Models;

/**
 * Name: Pipeline.php
 * 
 * Created: 18/05/2021
 * 
 * @author Mehdi Mehtarizadeh
 * 
 */

class Pipeline 
{
    protected $db;
    protected $table      = 'pipeline';
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

    function getPipelines(string $cols = null, array $conds = null, array $groupby = null, bool $isDistinct = false, int $limit = -1, int $offset = -1){
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

    public function getPipeline(int $id)
    {
        $pipeline = $this->getPipelines(null, ['id' => $id]);
        return $pipeline ? $pipeline[0] : null;
    }

    public function createPipeline(array $data)
    {
        $this->builder->insert($data);
    }

    public function updatePipeline(array $data, array $conds) {
        if ($conds)
        {
            $this->builder->where($conds);
        }
        $this->builder->update($data);
    }

    public function deletePipeline(int $pipeline_id)
    {
        $this->builder->where('id', $pipeline_id);
        $this->builder->delete();
    }

    public function getPipelinesByIds(array $ids)
    {
        $this->builder->select();
        $this->builder->whereIn('id', $ids);

        $query = $this->builder->get()->getResultArray();
        return $query;
    }

}