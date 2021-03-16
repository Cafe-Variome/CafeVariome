<?php namespace App\Models;

/**
 * Elastic.php
 * Created 26/07/2019
 * 
 * @author Owen Lancaster 
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 */

 use App\Models\Settings;
 use App\Libraries\ElasticSearch;
 use CodeIgniter\Model;
 use CodeIgniter\Database\ConnectionInterface;
 use GraphAware\Neo4j\Client\ClientBuilder;

class Elastic extends Model{

    protected $db;
    protected $builder;

    private $elasticInstance;

    public function  __construct(ConnectionInterface &$db = Null){
        if ($db != null) {
            $this->db =& $db;
        }
        else {
            $this->db = \Config\Database::connect();
        }
        $this->setting =  Settings::getInstance($this->db);

        $this->elasticInstance = new ElasticSearch([$this->setting->getElasticSearchUri()]);

        helper('filesystem');
    }

    /**
     * Delete Elastic Index - For a given source delete all its ElasticSearch Indices
     *
     * @param int $source_id - The Id of the Source
     * @return N/A
     */
    function deleteIndex(int $source_id) {

        $params = [];

        $prefix = $this->getTitlePrefix();
        $index_name = $prefix."_".$source_id."*";	    

        if($this->elasticInstance->indexExists($index_name)){
            $this->elasticInstance->deleteIndex($index_name);
        }
    }

    function buildCurlCommand($uri){
        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);
    }

    /**
     * Get VCF Pending - Get a list of all pending VCF files to add to MySQL for a given source
     *
     * @param int $source_id - The id of the source
     * @return array $vcf    - Full details needed for all VCF Files
     */
    function getvcfPending($source_id) {

        $this->builder = $this->db->table('uploaddatastatus');

        $this->builder->select('FileName,tissue,patient');
        $this->builder->like('FileName', '.vcf', 'before'); 
        $this->builder->where('Status', 'Pending');
        $this->builder->where('source_id', $source_id);
        $vcf = $this->builder->get()->getResultArray();
        return $vcf;
    }

    /**
     * VCF Wrap - We have finished inserting data for a VCF file and it is time to update the status table. 
     *
     * @param string $file   - The name of the file
     * @param int $source_id - The id of the source
     * @return N/A 
     */
    function vcfWrap($file, $source_id) {
        $this->builder = $this->db->table('uploaddatastatus');

        $now = date('Y-m-d H:i:s');
        $Status = "Success";
        $data = array(
            'uploadEnd' => $now,
            'Status' => $Status);
        $this->builder->where('FileName', $file);
        $this->builder->where('source_id', $source_id);
        $this->builder->update($data);
    }

    /**
     * getUnprocessedFilesForSource
     * For a given source get the number of files whose data has not been added to ElasticSearch
     *
     * @param int $source_id - The name of the source
     * @return int      - Count of how many Files there are which arent in ElasticSearch
     */
    function getUnprocessedFilesForSource(int $source_id): int {

        $this->builder = $this->db->table('uploaddatastatus');

        $this->builder->where('source_id', $source_id);
        $count = $this->builder->countAllResults(); 
        return $count;
    }

    /**
     * getUnaddedEAVs 
     * For a given source check whether there is any data in MySQL which isnt in ElasticSearch
     *
     * @param string $source_id  - The name of the source
     * @return int $noOfRecords    - Count of how many records there are which arent in ElasticSearch
     */
    function getUnaddedEAVs($source_id) {

        $this->builder = $this->db->table('eavs');

        $this->builder->where('elastic', 0);
        $this->builder->where('source_id', $source_id);

        $count = $this->builder->countAllResults();
        return $count;
    }

    /**
     * setElasticFlagForSource
     * Set elastic_status flag to 1 for a source.
     *
     * @param int $source_id - The name of the source
     * @return N/A
     */
    function setElasticFlagForSource(int $source_id) {
        $this->builder = $this->db->table('sources');

        $data = array('elastic_status' => 1);
        $this->builder->where('source_id', $source_id);
        $this->builder->update($data);
    }

    /**
     * getElasticFlagForSource
     * get elastic_status for a source from sources table
     *
     * @param int $source_id - The name of the source
     * @return array $query       - All columns for all files which are fresh
     */
    function getElasticFlagForSource(int $source_id):int {
        $this->builder = $this->db->table('sources');

        $this->builder->select('elastic_status');
        $this->builder->where('source_id', $source_id);
        $query = $this->builder->get()->getResult();
        return ($query) ? $query[0]->elastic_status : -1;
    }

    /**
     * Get Eavs Count - Count number of records for given source where elastic boolean is false
     *
     * @param int $source_id  - The id of the source
     * @return long $count  - The count of the records 
     */
    function getEAVsCountForSource(int $source_id): int{
        $this->builder = $this->db->table('eavs');

        $this->builder->where('source_id',$source_id);
        $this->builder->where('elastic',0);

        $count = $this->builder->countAllResults();
        return $count;
    }

    /**
     * resetElasticFlagForSourceEAVs
     * Set Elastic boolean to false for all data in a given source
     *
     * @param int $source_id  - The id of the source
     * @return N/A
     */
    function resetElasticFlagForSourceEAVs(int $source_id) {
        $this->builder = $this->db->table('eavs');

        $data = array(
                'elastic' => 0
        );
        $this->builder->where('source_id', $source_id);
        $this->builder->update($data);
    }

    /**
     * getTitlePrefix()
     * This funcion returns the first part of site_title variable in settings table in the database.
     * 
     * @author Mehdi Mehtarizadeh
     * 
     * @param void 
     * @return string
     */
    public function getTitlePrefix(): string
    {
        $title = $this->setting->settingData['site_title'];
        $title = preg_replace("/\s.+/", '', $title);
        $title = strtolower($title);

        $baseUrl = base_url();
        if(strpos($baseUrl, "http://") !== false){
            $baseUrl = str_replace('http://', '', $baseUrl);
        }
        elseif (strpos($baseUrl, 'https://') !== false) {
            $baseUrl = str_replace('https://', '', $baseUrl);
        }

        $segments = explode('/', $baseUrl);

        $prefix = count($segments) > 0 ? $segments[1] : $title;
 
        return $prefix;
    }
}