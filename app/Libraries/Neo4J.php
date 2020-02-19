<?php namespace App\Libraries;

/**
 * Neo4J.php
 * Created: 18/02/2020
 * 
 * @author Mehdi Mehtarizadeh
 */

class Neo4J 
{
    private $neo4jClient;
    private $status = false;

    public function __construct(string $neo4jUsername, string $neo4jPassword, string $baseNeo4jAddress = 'localhost', string $neo4jPort = '7474') {
        
        if (strpos($baseNeo4jAddress, 'http://') !== false) {
            $baseNeo4jAddress = str_replace("http://","",$baseNeo4jAddress);
        }
        if (strpos($baseNeo4jAddress, 'https://') !== false) {
            $baseNeo4jAddress = str_replace("https://","",$baseNeo4jAddress);
        }

        try {
            $this->neo4jClient =  \GraphAware\Neo4j\Client\ClientBuilder::create()
            ->addConnection('default', 'http://'. $neo4jUsername . ':' .$neo4jPassword .'@'.$baseNeo4jAddress.':'.$neo4jPort)
            ->build();
        } catch (\Exception $ex) {

        }
	
        
    }

    public function ping(): bool
    {
        try {
            $result = $this->neo4jClient->run('MATCH (n:Person) RETURN n'); //Just a sample query to make sure server is up and running.
            $this->status = true;
        } catch (\Exception $ex) {
            $this->status = false;
        }
        return $this->status;
    }
}
