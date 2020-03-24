<?php namespace App\Models;

/**
 * 
 */

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;


class Deliver extends Model{

    protected $db;
    protected $table      = 'hdr_query';
    protected $builder;

    protected $primaryKey = 'id';

    public function __construct(ConnectionInterface &$db = null){
        if ($db != null) {
            $this->db =& $db;
        }
        else{
            $this->db = \Config\Database::connect();
        }

        $this->builder = $this->db->table($this->table);

    }

    function addQueryRecord($query_id,$installation_key,$file_name) {
		$data = [
			"query_id" => $query_id,
			"installation_key" => $installation_key,
			"query_file" => $file_name];
		$this->builder->insert($data);
	}

	function retrieveInstallQueryId($query_id, $bool = false) {
		$this->builder->select('installation_key');

        $this->builder->where('query_id', $query_id);
		$installation_keys = $this->builder->get()->getResultArray();
		if (!$bool) {
			$output = [];
			foreach ($installation_keys as $key ) {
				array_push($output, $key['installation_key']);
			}
		}
		else {
			$output = "";
			for ($i=0; $i < count($installation_keys); $i++) { 
				if ($i == 0) {
					$output = $installation_keys[$i]['installation_key'];
				}
				else {
					$output = $output . "," . $installation_keys[$i]['installation_key'];
				}
			}
		}
		
		return $output;
	}
}