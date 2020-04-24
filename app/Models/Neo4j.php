<?php namespace App\Models;

/**
 * Neo4j.php
 * 
 * Created 09/08/2019
 * 
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 * @author Owen Lancaster
 * 
 * This class handles operations for Neo4j database.
 * 
 */

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;
use GraphAware\Neo4j\Client\ClientBuilder;
use App\Models\Settings;


class Neo4j extends Model{

    private $setting;

    private $neo4jUsername;
    private $neo4jPassword;
    private $neo4jAddress;
    private $neo4jPort;

	public function __construct(ConnectionInterface &$db = Null){

        if ($db != null) {
            $this->db =& $db;
        }
        else {
            $this->db = \Config\Database::connect();
		}
        $this->setting =  Settings::getInstance();

        $this->neo4jUsername = $this->setting->settingData['neo4j_username'];
        $this->neo4jPassword = $this->setting->settingData['neo4j_password'];
        $this->neo4jAddress = $this->setting->settingData['neo4j_server'];
        $this->neo4jPort = $this->setting->settingData['neo4j_port'];
    }

    public function toUpdate($data,$source_name) {

        $baseNeo4jAddress = $this->neo4jAddress;
        if (strpos($baseNeo4jAddress, 'http://') !== false) {
            $baseNeo4jAddress = str_replace("http://","",$baseNeo4jAddress);
        }
        if (strpos($baseNeo4jAddress, 'https://') !== false) {
            $baseNeo4jAddress = str_replace("https://","",$baseNeo4jAddress);
        }

        $client = ClientBuilder::create()
        ->addConnection('default', 'http://'. $this->neo4jUsername . ':' .$this->neo4jPassword .'@'.$baseNeo4jAddress.':'.$this->neo4jPort)
        ->setDefaultTimeout(60)
        ->build();	    
        $keys = array_keys($data);
        $batch = md5(uniqid(rand(),true));	
        $tx = $client->transaction();
        error_log(print_r($data,1));
        foreach ($keys as $key) {
            $query = 'MATCH (n:Subject{subjectid:"'.$key.'"}) RETURN n.subjectid as id';
            $result = $client->run($query);
            $exists = false;
            foreach ($result->records() as $record) {
                $exists = true;
            }
            // return;
            if (!$exists) {
                error_log("it doesnt");
                $tx->push("CREATE (n:Subject{ subjectid: '".$key."', source: '".$source_name."', batch: '".$batch."' })");
                for ($i=0; $i < count($data[$key]); $i++) { 
                    if ($data[$key][$i]['negated']) {
                        $tx->push("MATCH (a:Subject),(b:HPOterm) WHERE a.subjectid = '".$key."' AND b.hpoid = '".$data[$key][$i]['hpo']."' CREATE (a)<-[r:NOT_PHENOTYPE_OF]-(b)");
                    }
                    else {
                        $tx->push("MATCH (a:Subject),(b:HPOterm) WHERE a.subjectid = '".$key."' AND b.hpoid = '".$data[$key][$i]['hpo']."' CREATE (a)<-[r:PHENOTYPE_OF]-(b)");
                    }
                }
            }
            else {
                error_log("it exists");
            }
        }
        $results = $tx->commit();
    }

    
    function deleteSource(int $source_id) {

        $baseNeo4jAddress = $this->neo4jAddress;
        if (strpos($baseNeo4jAddress, 'http://') !== false) {
            $baseNeo4jAddress = str_replace("http://","",$baseNeo4jAddress);
        }
        if (strpos($baseNeo4jAddress, 'https://') !== false) {
            $baseNeo4jAddress = str_replace("https://","",$baseNeo4jAddress);
        }
        $sourceModel = new Source($this->db);
        $client = ClientBuilder::create()
        ->addConnection('default', 'http://'. $this->neo4jUsername . ':' .$this->neo4jPassword .'@'.$baseNeo4jAddress.':'.$this->neo4jPort)
        ->setDefaultTimeout(60)
        ->build();
        $source_name = $sourceModel->getSourceNameByID($source_id);
        $query = 'MATCH (n:Subject { source: "'.$source_name.'" }) DETACH DELETE n';
        $result = $client->run($query);
    }

}