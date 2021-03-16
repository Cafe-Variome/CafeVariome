<?php namespace App\Models;

/**
 * Phenotype.php
 * 
 * Created: 09/08/2019
 * 
 * @author Owen Lancaster
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 * 
 * This class handles data operations on Phenotypes in EAVs table.
 * 
 */

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;
use App\Libraries\CafeVariome\Net\ServiceInterface;


 class Phenotype extends Model{

    protected $db;
    protected $builder;

    private $serviceInterface;

    public function __construct(ConnectionInterface &$db = null)
    {
        if ($db != null) {
            $this->db =& $db;
        }
        else {
            $this->db = \Config\Database::connect();
        }

        $this->serviceInterface = new ServiceInterface();
    }

    /**
     * localPhenotypesLookupValues
     * Create Local Phenotype Lookup Values and insert them into database.
     * 
     * @param int $source_id
     * @param string $network_key
     * 
     * @return array localPhenoTypes
     */
    function localPhenotypesLookupValues(int $source_id, string $network_key) {

        $eavModel = new EAV();
        $sourceModel = new Source();

        $eavCount = $eavModel->getEAVs('count(*) as totalEAVs', ['source_id' => $source_id]);
        $totalEAVRecords = $eavCount[0]['totalEAVs'];

        $source_name = $sourceModel->getSourceNameByID($source_id);
        
        $data = [];
        $tempLocalPhenotypes = [];
        $recordsProcessed = 0;

        if ($totalEAVRecords > 0) {
            $this->serviceInterface->ReportProgress($source_id, $recordsProcessed, $totalEAVRecords, 'elasticsearchindex', 'Processing attributes and values for: ' . $source_name);
        }

        $batchSize = 10000;

        for ($i=0; $i < $totalEAVRecords; $i+=$batchSize) { 
            $data = $eavModel->getEAVsForSource($source_id, $batchSize, $i);
            $this->swapLocalPhenotypes($data, $tempLocalPhenotypes, $network_key);
            $recordsProcessed += count($data);
            $this->serviceInterface->ReportProgress($source_id, $recordsProcessed, $totalEAVRecords, 'elasticsearchindex');
        }

        return $tempLocalPhenotypes;
    }

    private function swapLocalPhenotypes(array $data, & $tempLocalPhenotypes, int $network_key)
    {
        foreach ($data as $d) {

            $attr = $d['attribute'];
            $value = $d['value'];
            
            if(strlen($value) > 229) continue;

            if(is_numeric($value)) {            
                $sigs = 4;
                if(is_float($value) && floatval($value)) {
                    if($value < 0) {
                        $value = round($value * -1, $sigs) * -1;
                    } else {
                        $value = round($value, $sigs);
                    }
                }
            }

            $value = (string)$value;      

            $local_phenotypes = [];

            foreach ($tempLocalPhenotypes as $tlp) {
                if ($tlp['phenotype_attribute'] == $attr) {
                    array_push($local_phenotypes, $tlp);
                }
            }
            
            if(count($local_phenotypes) > 0) {
                $lastLP = array_pop($local_phenotypes);
                if(in_array($value, explode("|" , $lastLP['phenotype_values'])) || (strpos($lastLP['phenotype_values'], 'Not all values displayed|') !== false)) continue;
                else {
                    // Allow displaying of all values
                    $val = $lastLP['phenotype_values'] . $value . "|";
                    $tempLocalPhenotypes[$attr]['phenotype_values'] = $val;
                    $tempLocalPhenotypes[$attr]['phenotype_attribute'] = $attr;
                }
            } else {
                $value = $value . "|";

                $tempLocalPhenotypes[$attr] = ["network_key" => $network_key, "phenotype_attribute" => $attr, "phenotype_values" => $value];
            }
        }
    }
    
 }