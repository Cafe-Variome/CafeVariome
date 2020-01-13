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
 
 use CodeIgniter\Model;
 use CodeIgniter\Database\ConnectionInterface;
 use GraphAware\Neo4j\Client\ClientBuilder;

class Elastic extends Model{

    protected $db;
    protected $builder;

    public function  __construct(ConnectionInterface &$db){
        $this->db =& $db;
        $this->setting =  Settings::getInstance($this->db);
        helper('filesystem');
    }

    /**
     * Delete Elastic Index - For a given source delete all its ElasticSearch Indices
     *
     * @param int $source_id - The Id of the Source
     * @return N/A
     */
    function deleteElasticIndex($source_id) {

        $params = [];

        $title = $this->getTitlePrefix();

        $index_name = $title."_".$source_id."*";	    
        $uri = $this->setting->settingData['elastic_url']."/".$index_name;

        try {
            $this->buildCurlCommand($uri);
        }	  
        catch (\Exception $e) {
            error_log("No Indices exist for source: ". $source_id);
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
        $this->builder->where('source', $source_id);

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

        $this->builder->where('source',$source_id);
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
        $this->builder->where('source', $source_id);
        $this->builder->update($data);
    }

    /**
     * Regenerate Federated Phenotype Attributes And Values List - Update Attributes and Values lists for given source
     *
     * @param string $source_name - The source we performing this operation for
     * @return N/A
     */
    public function regenerateFederatedPhenotypeAttributeValueList($source_id) {
        // Load Models
        $sourceModel = new Source($this->db);
        $networkModel = new Network($this->db);
        $phenotypeModel = new Phenotype($this->db);
        
        $sourceModel->toggleSourceLock($source_id);	
        //$result = $networkModel->getNetworkAndTheirSourcesForThisInstallation();
        $result = $networkModel->getNetworkSourcesForCurrentInstallation();

        $phenotypeModel->deleteAllLocalPhenotypesLookup();

        delete_files("resources/phenotype_lookup_data/");

        if(isset($result['error'])) return;

        foreach ($result as $row) {
            try {
                $data = $phenotypeModel->localPhenotypesLookupValues($row['source_id'], $row['network_key']);
            } catch (\Exception $ex) {
                var_dump($ex);
                exit;
            }

            $json_data = array();
            foreach ($data as $d) {
                $json_data[] = array("attribute" => $d['phenotype_attribute'], "value" => rtrim($d['phenotype_values'], "|"));
            }
            
            if (!file_exists('resources/phenotype_lookup_data/')) {
                mkdir('resources/phenotype_lookup_data/', 777, true);
            }
            //Check the length of the data array, if it is empty, don't append it to the file.
            if (count($json_data) > 0) {
                //Data must be written to the file in every iteration, in case the file is overwritten, some data is lost.
                file_put_contents("resources/phenotype_lookup_data/" . $row['network_key'] . ".json", json_encode($json_data), FILE_APPEND);
            }
        }

        //HPO Ancestry JSON FILE

        $neo4jUsername = $this->setting->settingData['neo4j_username'];
        $neo4jPassword = $this->setting->settingData['neo4j_password'];
        $neo4jAddress = $this->setting->settingData['neo4j_server'];
        $neo4jPort = $this->setting->settingData['neo4j_port'];

        $baseNeo4jAddress = $neo4jAddress;
        if (strpos($baseNeo4jAddress, 'http://') !== false) {
            $baseNeo4jAddress = str_replace("http://","",$baseNeo4jAddress);
        }
        if (strpos($baseNeo4jAddress, 'https://') !== false) {
            $baseNeo4jAddress = str_replace("https://","",$baseNeo4jAddress);
        }

        $neo4jClient =  ClientBuilder::create()
        ->addConnection('default', 'http://'. $neo4jUsername . ':' .$neo4jPassword .'@'.$baseNeo4jAddress.':'.$neo4jPort)
        ->setDefaultTimeout(60)
        ->build();	    
        
        $result = $networkModel->getAllNetworksSourcesBySourceId($source_id);
        $sourceslist = []; // NEED to DO THIS per NETWORK!!!!

        foreach ($result as $nkey => $row) {
            $network = $nkey;
            if (!isset($sourceslist[$network])) $sourceslist[$network] = [];
            array_push($sourceslist[$network], $row[0]);
        }

        foreach ($sourceslist as $network => $sourcelist) {
            $hpo_terms = $phenotypeModel->getHPOTerms($sourcelist);

            error_log("HPO Term Numbers: " . count($hpo_terms));

            foreach ($hpo_terms as $term){
                $query = "MATCH (c:HPOterm{hpoid:\"".$term."\"})-[:IS_A]->(p:HPOterm) RETURN c.termname as termname, p.hpoid as ph";
                $result = $neo4jClient->run($query);

                $pars = [];
                $termname = '';
                foreach ($result->getRecords() as $record) {
                    array_push($pars, $record->value('ph'));
                    $termname = $record->value('termname');
                }  
                $term .= ' (' . $termname . ')';
                $ancestors = $this->collect_ids_neo4j('', $pars);
                $hpo[$term] = $ancestors;

                $flag = false;
                while(!$flag) {
                    $flag = true;
                    $parents = [];
                    foreach ($hpo[$term] as $key => $ancestor) {
                        $temp = explode('|', $ancestor);
                        $t = end($temp);

                        if($t !== 'HP:0000001') {
                            $query = "MATCH (c:HPOterm{hpoid:\"".$t."\"})-[:IS_A]->(p:HPOterm) RETURN c.termname as termname, p.hpoid as ph";
                            $result = $neo4jClient->run($query);

                            $pars = [];
                            $termname = '';
                            foreach ($result->getRecords() as $record) {
                                array_push($pars, $record->value('ph'));
                            } 
                            $parents = array_merge($parents, $this->collect_ids_neo4j($ancestor, $pars));
                            $flag = false;
                        } else {
                            $parents[] = $ancestor;
                        }
                    }
                    $hpo[$term] = $parents;
                }
            }
            foreach($hpo as $term => $ancestory) {
                $hpo[$term] = implode('||', $ancestory);
            }
            //Check the length of the data array, if it is empty, don't append it to the file.
            if (count($hpo) > 0) {
                //Data must be written to the file in every iteration, in case the file is overwritten, some data is lost.
                file_put_contents("resources/phenotype_lookup_data/" . $network . "_hpo_ancestry.json", json_encode($hpo), FILE_APPEND);
            }
        }

        return;
    }

    function collect_ids_neo4j($ancestor, $data) {
        $arr = [];
        foreach ($data as $d) {
            $arr[] = $ancestor === '' ? $d : $ancestor . '|'. $d;
        }
        return $arr;
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
    public function getTitlePrefix(): string{
        $title = $this->setting->settingData['site_title'];
        $title = preg_replace("/\s.+/", '', $title);
        $title = strtolower($title); 
        return $title;
    }
}