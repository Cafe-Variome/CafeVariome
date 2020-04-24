<?php namespace App\Libraries;

/**
 * ElasticSearch.php
 * 
 * Created: 04/09/2019
 * @author Mehdi Mehtarizadeh
 * 
 * This is a wrapper class for Elasticsearch php client. 
 * The aim is to create simple visual tools to monitor elastic search and to provide functionality that 
 * original php client doesn't.
 * @see https://github.com/elastic/elasticsearch-php
 */

class ElasticSearch{

    private $client;
    private $hosts;

    private $errorFlag;

    function __construct(array $hosts){
        $this->hosts = $hosts;
        try {
            $this->client = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
        } catch (\Exception $ex) {
            throw new \Exception("Failed to create client instance.");
        }
    }

    /**
     * indexExists(string $name)
     * 
     * @param string name of Index 
     * 
     * @return true|false
     */
    function indexExists(string $name):bool{
        return ($this->getIndex($name) != null);
    }

    /**
     * getIndex(string $name)
     * 
     * @param string name of Index to retrieve
     * 
     * @return array|null
     */
    function getIndex(string $name)
    {
        $params = ['index' => $name];

        try {
            $response = $this->client->indices()->getSettings($params);
            return $response;
        } catch (\Exception $ex) {
            return null;
        }
    }

    /**
     * getIndices()
     * 
     * gets all indices in Elasticsearch.
     * 
     * @return array|null
     */
    function getIndices(){
        $params = ['index' => '*'];

        try {
            $response = $this->client->indices()->getSettings($params);
            return $response;
        } catch (\Exception $ex) {
            return null;
        }
    }

    /**
     * deleteIndex()
     * 
     * Deletes an index in Elasticsearch.
     * 
     * @return array|null
     */
    public function deleteIndex(string $name)
    {
        $params = ['index' => $name];

        try {
            $response = $this->client->indices()->delete($params);
            return $response;
        } catch (\Exception $ex) {
            return null;
        }
    }

    /**
     * ping()
     * 
     * pings elastic server hosts and returns true if they respond, false otherwise.
     * 
     * @return bool
     */
    function ping():bool{
        try {
            $status = $this->client->ping();
            return $status;

        } catch (\Exception $ex) {
            return false;
        }
    }
}