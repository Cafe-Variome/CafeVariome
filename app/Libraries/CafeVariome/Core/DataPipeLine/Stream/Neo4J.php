<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Stream;

/**
 * Neo4J.php
 * Created: 18/02/2020
 * 
 * @author Mehdi Mehtarizadeh
 */

use CodeIgniter\Database\ConnectionInterface;
use GraphAware\Neo4j\Client\ClientBuilder;
use App\Models\Settings;
use App\Libraries\CafeVariome\Net\ServiceInterface;

class Neo4J 
{
    private $neo4jClient;
    private $status = false;
    private $setting;

    private $neo4jUsername;
    private $neo4jPassword;
    private $neo4jAddress;
    private $neo4jPort;

    private $transactionStack;

    public function __construct() {
        
        $this->setting =  Settings::getInstance();

        $this->neo4jUsername = $this->setting->getNeo4JUserName();
        $this->neo4jPassword = $this->setting->getNeo4JPassword();
        $this->neo4jAddress = $this->setting-> getNeo4JUri();
        $this->neo4jPort = $this->setting->getNeo4JPort();

        $this->neo4jClient = $this->getClient();
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

    private function getClient()
    {
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
        
        return $client;
    }

    public function MatchHPO_IS_A(string $hpoTerm)
    {
        $query = "MATCH (c:HPOterm{hpoid:\"".$hpoTerm."\"})-[:IS_A]->(p:HPOterm) RETURN c.termname as termname, p.hpoid as ph";
        return $this->neo4jClient->run($query);
    }

    public function GetAncestors(string $hpoTerm): array
    {
        $pars = [];
        $parents = [];
        $termname = '';
        $matchedTerms = $this->MatchHPO_IS_A($hpoTerm);

        foreach ($matchedTerms->getRecords() as $record) {
            array_push($pars, $record->value('ph'));
            $termname = $record->value('termname');
            $parents[$record->value('ph')] = $record->value('termname');
        }  

        $last_ancestor = '';
        $i = 0;
        while (count($pars) > 0) {
                $ancestor = $pars[$i];
                $last_ancestor = $ancestor;
                if($ancestor !== 'HP:0000001') {
                    $matchedTerms = $this->MatchHPO_IS_A($ancestor);
                    foreach ($matchedTerms->getRecords() as $record) {
                        array_push($pars, $record->value('ph'));
                        $parents[$last_ancestor] = $record->value('termname');
                        $last_ancestor = $record->value('ph');
                        $processed_ancestor_key = array_search($ancestor, $pars);
                        unset($pars[$processed_ancestor_key]);
                        $i ++;
                    } 
                }
                else {
                    $parents[$ancestor] = 'All';
                    $pars = [];
                }
        }

        return $parents;
    }

    public function InsertSubject(string $subject_id, string $source_name, string $batch, bool $allow_duplicate = false)
    {
        $this->transactionStack = $this->transactionStack ? $this->transactionStack : $this->neo4jClient->transaction();
        if (!$allow_duplicate) {
            $query = 'MATCH (n:Subject{subjectid:"'.$subject_id.'"}) RETURN n.subjectid as id';
            $result = $this->neo4jClient->run($query);
            $exists = count($result->records()) > 0 ? true : false;
            if ($exists) {
                return;
            }
        }
        $this->transactionStack->push("CREATE (n:Subject{ subjectid: '".$subject_id."', source: '".$source_name."', batch: '".$batch."' })");
    }

    public function ConnectSubject(string $subject_id, string $node_type, string $node_key, string $node_id, string $relationship_label)
    {
        $this->transactionStack->push("MATCH (a:Subject),(b:" . $node_type . ") WHERE a.subjectid = '" . $subject_id . "' AND b." . $node_key . " = '" . $node_id . "' CREATE (a)<-[r:" . $relationship_label . "]-(b)");
    }

    public function InsertSubjects(array $data, string $source_name, string $batch)
    {
        $serviceInterface = new ServiceInterface();
        $sourceModel = new Source();

        $keys = array_keys($data);

        $source_id = $sourceModel->getSourceIDByName($source_name);
        $this->transactionStack = $this->transactionStack ? $this->transactionStack : $this->neo4jClient->transaction();

        $subjectsAdded = 0;
        if (count($keys) > 0) {
            $serviceInterface->ReportProgress($source_id, $subjectsAdded, count($keys), 'elasticsearchindex', 'Adding subjects to Neo4J');
        }

        foreach ($keys as $subject_id) {
            $this->InsertSubject($subject_id, $source_name, $batch);
            $serviceInterface->ReportProgress($source_id, $subjectsAdded++, count($keys), 'elasticsearchindex');
        }
        $this->commitTransaction(true);
    }

    public function ConnectSubjects(array $data, string $node_type, string $node_key, string $data_type, int $source_id)
    {
        $serviceInterface = new ServiceInterface();

        $keys = array_keys($data);

        $this->transactionStack = $this->transactionStack ? $this->transactionStack : $this->neo4jClient->transaction();

        $subjectsConnected = 0;
        if (count($keys) > 0) {
            $serviceInterface->ReportProgress($source_id, $subjectsConnected, count($keys), 'elasticsearchindex', 'Connecting subjects in Neo4J');
        }

        foreach ($keys as $subject_id) {
            for ($i=0; $i < count($data[$subject_id]); $i++) { 
                $data_element = strtolower($data_type) == 'hpo' ? $data[$subject_id][$i]['hpo'] : $data[$subject_id][$i]['orpha'];
                if (strtolower($data_type) == 'hpo' && $data[$subject_id][$i]['negated']) {
                    $this->ConnectSubject($subject_id, $node_type, $node_key,  $data_element, 'NOT_PHENOTYPE_OF');
                }
                else {
                    $this->ConnectSubject($subject_id, $node_type, $node_key, $data_element, 'PHENOTYPE_OF');
                }
            }

            $subjectsConnected++;
            $serviceInterface->ReportProgress($source_id, $subjectsConnected, count($keys), 'elasticsearchindex');
        }
        $this->commitTransaction(true);
    }

    public function deleteSource(int $source_id) {

        $sourceModel = new Source();
        $source_name = $sourceModel->getSourceNameByID($source_id);
        $query = 'MATCH (n:Subject { source: "'.$source_name.'" }) DETACH DELETE n';
        $result = $this->neo4jClient->run($query);
    }

    private function commitTransaction(bool $destroy = false)
    {
        $this->transactionStack->commit();
        if($destroy)
        {
            $this->destroyTransaction();
        }
    }

    private function destroyTransaction()
    {
        $this->transactionStack = null;
    }

    private function collectIDs(string $ancestor, array $data) {
        $arr = [];
        foreach ($data as $d) {
            $arr[] = $ancestor === '' ? $d : $ancestor . '|'. $d;
        }
        return $arr;
    }
}
