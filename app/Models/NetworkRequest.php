<?php namespace App\Models;

/**
 * Name: NetworkRequest.php
 * Created: 2/12/2019
 * 
 * @author Mehdi Mehtarizadeh
 * 
 * 
 * 
 */

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;

class NetworkRequest extends Model 
{
    protected $db;
    protected $table      = 'network_requests';
    protected $builder;

    protected $primaryKey = 'id';
    
    private $response;

	public function __construct(ConnectionInterface &$db = null){

        if ($db != null) {
            $this->db =& $db;
        }
        else {
            $this->db = \Config\Database::connect();
        }
        $this->setting = Settings::getInstance($this->db);
        
        $this->builder = $this->db->table($this->table);

    }

    function getNetworkRequests(string $cols = null, array $conds = null, array $groupby = null, bool $isDistinct = false, int $limit = -1, int $offset = -1){
	
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

    public function createNetworkRequest(array $data): bool
    {
        try {
            $this->builder->insert($data);
            $this->initiateResponse(1);
            return true;
        } catch (\Exception $ex) {
            $this->initiateResponse(0);
            $this->setResponseMessage($ex->getMessage());
            error_log($ex->getMessage());
            return false;
        }
    }

    public function updateNetworkRequests(array $data, array $conds = null) {
        if ($conds) {
            $this->builder->where($conds);
        }
        return $this->builder->update($data);
    }
}
