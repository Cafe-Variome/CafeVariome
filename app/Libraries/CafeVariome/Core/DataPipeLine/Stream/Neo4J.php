<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Stream;

/**
 * Neo4J.php
 * Created: 18/02/2020
 *
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 */

use App\Models\Source;
use App\Models\Settings;
use App\Libraries\CafeVariome\Net\ServiceInterface;
use Laudis\Neo4j\Authentication\Authenticate;
use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Databags\Statement;

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
        $this->neo4jAddress = $this->setting->getNeo4JUri();
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

    private function getClient(): \Laudis\Neo4j\Contracts\ClientInterface
	{
    	$protocol = 'http';
        if (strpos($this->neo4jAddress, 'https://') !== false) {
			$protocol = 'https';
        }

        return ClientBuilder::create()
			->withDriver($protocol, $this->neo4jAddress . ':' . $this->neo4jPort, Authenticate::basic($this->neo4jUsername, $this->neo4jPassword)) // creates an http driver
			->withDefaultDriver($protocol)
			->build();
    }

	public function runQuery(string $query)
	{
		return $this->neo4jClient->run($query);
    }

    public function MatchHPO_IS_A(string $hpoTerm)
    {
        $query = "MATCH (c:HPOterm{hpoid:\"".$hpoTerm."\"})-[:IS_A]->(p:HPOterm) RETURN c.term as termname, p.hpoid as ph";
        return $this->neo4jClient->run($query);
    }

    public function GetAncestors(string $hpoTerm): array
    {
        $pars = [];
        $parents = [];
        $termname = '';
        $matchedTerms = $this->MatchHPO_IS_A($hpoTerm);

        foreach ($matchedTerms as $record) {
            array_push($pars, $record->get('ph'));
            $termname = $record->get('termname');
            $parents[$record->get('ph')] = $record->get('termname');
        }

        $last_ancestor = '';
        $i = 0;
        while (count($pars) > 0) {
                $ancestor = $pars[$i];
                $last_ancestor = $ancestor;
                if($ancestor !== 'HP:0000001') {
                    $matchedTerms = $this->MatchHPO_IS_A($ancestor);
                    foreach ($matchedTerms as $record) {
                        array_push($pars, $record->get('ph'));
                        $parents[$last_ancestor] = $record->get('termname');
                        $last_ancestor = $record->get('ph');
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
		$this->transactionStack = $this->transactionStack ? $this->transactionStack : $this->neo4jClient->beginTransaction();
		if (!$allow_duplicate) {
			$query = 'MATCH (n:Subject{subjectid:"'.$subject_id.'"}) RETURN n.subjectid as id';
			$result = $this->neo4jClient->run($query);
			$exists = $result->count() > 0 ? true : false;
			if ($exists) {
				return;
			}
		}
		$this->transactionStack->runStatement(Statement::create("CREATE (n:Subject{ subjectid: '".$subject_id."', source: '".$source_name."', batch: '".$batch."' })"));
	}

	public function ConnectSubject(string $subject_id, string $node_type, string $node_key, string $node_id, string $relationship_label)
	{
		$this->transactionStack->runStatement(Statement::create("MATCH (a:Subject),(b:" . $node_type . ") WHERE a.subjectid = '" . $subject_id . "' AND b." . $node_key . " = '" . $node_id . "' CREATE (a)<-[r:" . $relationship_label . "]-(b)"));
	}

	public function InsertSubjects(array $data, string $source_name, string $batch)
	{
		$serviceInterface = new ServiceInterface();
		$sourceModel = new Source();

		$source_id = $sourceModel->getSourceIDByName($source_name);
		$this->transactionStack = $this->transactionStack ? $this->transactionStack : $this->neo4jClient->beginTransaction();

		$subjectsAdded = 0;
		if (count($data) > 0) {
			$serviceInterface->ReportProgress($source_id, $subjectsAdded, count($data), 'elasticsearchindex', 'Adding subjects to Neo4J');
		}

		for ($i = 0; $i < count($data); $i++) {
			$this->InsertSubject($data[$i], $source_name, $batch);
			$serviceInterface->ReportProgress($source_id, $subjectsAdded++, count($data), 'elasticsearchindex');
		}
		$this->commitTransaction(true);
	}

	public function ConnectSubjects(array $data, string $node_type, string $node_key, string $data_type, int $source_id)
	{
		$serviceInterface = new ServiceInterface();

		$this->transactionStack = $this->transactionStack ? $this->transactionStack : $this->neo4jClient->beginTransaction();

		$subjectsConnected = 0;
		if (count($data) > 0) {
			$serviceInterface->ReportProgress($source_id, $subjectsConnected, count($data), 'elasticsearchindex', 'Connecting subjects in Neo4J');
		}

		$relationship_label = '';
		switch (strtolower($data_type)){
			case 'hpo':
			case 'orpha':
				$relationship_label = 'PHENOTYPE_OF';
				break;
			case 'negated_hpo':
				$relationship_label = 'NOT_PHENOTYPE_OF';
				break;
		}

		for ($i = 0; $i < count($data); $i++) {
			$this->ConnectSubject($data[$i]['subject_id'], $node_type, $node_key, strtoupper($data[$i]['value']), $relationship_label);
			$subjectsConnected++;
			$serviceInterface->ReportProgress($source_id, $subjectsConnected, count($data), 'elasticsearchindex');
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
}
