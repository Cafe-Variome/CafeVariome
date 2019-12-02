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
    protected $table      = 'network_request';
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

    private function initiateResponse(int $status, array $data = null)
    {
        $this->response = new NetworkAPIResponse($status, $data);
    }

    private function setResponseMessage(string $message)
    {
        $this->response->setMessage($message);
    }

    public function getResponse(): NetworkAPIResponse
    {
        return $this->response;
    }

    public function getResponseArray(): array
    {
        return $this->response->toArray();
    }

    public function getResponseJSON(): string
    {
        return $this->response->toJSON();
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
    

}
