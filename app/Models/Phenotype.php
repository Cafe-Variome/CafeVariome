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

 class Phenotype extends Model{
    protected $db;
    protected $builder;

    public function __construct(ConnectionInterface &$db){

        $this->db =& $db;
    }

    function deleteAllLocalPhenotypesLookup() {
        $this->builder = $this->db->table('local_phenotypes_lookup');
        $this->builder->emptyTable();
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
        $eavModel = new EAV($this->db);

        $data = $eavModel->getEAVsForSource($source_id);

        $tempLocalPhenotypes = [];

        foreach ($data as $d) {
            $attr = $d['attribute'];
            $value = $d['value'];
            
            if(strlen($value) > 229) continue;

            if(is_numeric($value)) {            
				$sigs = 4;
            	if(floatval($value)) {
            		if($value < 0) {
            			$value = $this->RoundSigDigs($value * -1, $sigs) * -1;
            		} else {
            			$value = $this->RoundSigDigs($value, $sigs);
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
        foreach ($tempLocalPhenotypes as $tlp) {
            $attr = $tlp['phenotype_attribute'];
            $value = $tlp['phenotype_values'];
            $sql = "INSERT INTO `local_phenotypes_lookup`(`network_key`, `phenotype_attribute`, `phenotype_values`) VALUES ('$network_key', '$attr', '$value')";
            $this->db->query($sql);
        }
        return $tempLocalPhenotypes;
    }

    function updateLocalPhenoTypes(array $updateData,array $conds){

        
        $this->builder = $this->db->table('local_phenotypes_lookup');
        if($updateData) {
            if($conds){
                $this->builder->where($conds);
            }
            $this->builder->update($updateData);
        }
        
    }

    function createLocalPhenoTypeLookup($data){
        $this->builder = $this->db->table('local_phenotypes_lookup');
        $this->builder->insert($data);
    }

    function getHPOTerms($source_ids) { //edited may13 2019 to remove match for phenotypes_id
        $this->builder = $this->db->table('eavs');
        $this->builder->select('value');
        $this->builder->distinct();
        $this->builder->whereIn('source', $source_ids);
        $this->builder->like('value', 'HP:', 'after');

        $terms = $this->builder->get()->getResultArray();

        $hpo_terms = [];
		foreach ($terms as $term) {
			$hpo_terms[] = $term['value'];
		}
		return $hpo_terms;
	}

 }