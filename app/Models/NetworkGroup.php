<?php namespace App\Models;

/**
 * NetworkGroup.php
 * 
 * Created: 20/08/2019
 * 
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 * 
 * This class handles operations on NetworkGroup entities and network_groups table.
 */

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;


class NetworkGroup extends Model{
    
	protected $db;
    protected $table      = 'network_groups';
    protected $builder;

    protected $primaryKey = 'id';


    function __construct(ConnectionInterface &$db = Null){
        if ($db != null) {
            $this->db =& $db;
        }
        else {
            $this->db = \Config\Database::connect();
        }
        $this->setting =  Settings::getInstance();
        $this->builder = $this->db->table($this->table);
    }

    /**
	 * getNetworkGroups
     * 
	 * General function to get fetch data from network_groups table.
     * 
     * @author Mehdi Mehtarizadeh
	 */
	function getNetworkGroups(string $cols = null, array $conds = null, array $groupby = null, bool $includeNetworks = false, bool $isDistinct = false, int $limit = -1, int $offset = -1){
		
		if ($cols) {
            $this->builder->select($cols);
        }
        if ($conds) {
            $this->builder->where($conds);
        }
        if ($groupby) {
            $this->builder->groupBy($groupby);
        }
        if ($includeNetworks) {
            $this->builder->join('networks', $this->table.'.network_key = networks.network_key');
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
	 * createNetworkGroup
	 */
	function createNetworkGroup(array $data) {
		$this->builder->insert($data);
		$insert_id = $this->db->insertID();
		return $insert_id;
    }
    
    function deleteNetworkGroup(int $id){
        $this->builder = $this->db->table($this->table); // Do not remove this line! @see hasSource(int $group_id)
        $this->builder->where('id', $id);
        $this->builder->delete();
    }

    /**
     * hasSource
     * 
     * @param int $group_id 
     * @return bool true if networkgroup has any sources assigned, false otherwise
     */
    function hasSource(int $group_id): bool{
        $this->builder = $this->db->table('network_groups_sources');
        $this->builder->where('group_id', $group_id);

        return ($this->builder->countAllResults() > 0)? true : false;
    }

    public function getRemoteNetworkGroups()
    {
        $networkGroups = $this->getNetworkGroups(null, null, ['name', 'id'], false);
        $localNetworkGroups = $this->getNetworkGroups(null, null, ['name', 'id'], true);
        $lngIds = [];
        foreach ($localNetworkGroups as $lng) {
            array_push($lngIds, $lng['id']);
        }
        if (count($lngIds) > 0) {
            $this->builder->whereNotIn('id', $lngIds);
        }

        $query = $this->builder->get()->getResultArray();
        return $query;
    }

    public function getMasterNetworkGroupsBySourceId(int $source_id): array
    {
        $this->builder->select($this->table . '.id');
        $this->builder->join('network_groups_sources', 'network_groups_sources.group_id = ' . $this->table . '.id');
        $this->builder->where('network_groups_sources.source_id', $source_id);
        $this->builder->where($this->table . '.group_type', 'master');

        $query = $this->builder->get()->getResultArray();

        $ids = [];

        foreach ($query as $row) {
            array_push($ids, $row['id']);
        }
        
        return $ids;
    }
 }