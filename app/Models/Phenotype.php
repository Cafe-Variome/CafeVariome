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

    function localPhenotypesLookupValues($source_id, $network_key) {
        $eavModel = new EAV($this->db);
        
        $data = $eavModel->getEAVsForSource($source_id);

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
            

            $data2 = $this->getLocalPhenotypes(null,['network_key'=> $network_key, 'phenotype_attribute'=>$attr]);

            if(count($data2) > 0) {
                if(in_array($value, explode("|" , $data2[0]['phenotype_values'])) || (strpos($data2[0]['phenotype_values'], 'Not all values displayed|') !== false)) continue;
                else {
                    // Allow displaying of all values
                    $val = $data2[0]['phenotype_values'] . $value . "|";
                    $localPhenoTypeUpdateData = ["phenotype_values"=>$val];
                    $this->updateLocalPhenoTypes($localPhenoTypeUpdateData, ["lookup_id"=>$data2[0]['lookup_id']]);
                }
            } else {
                $value = $value . "|";
                $data = array(
	                'network_key' =>  $network_key,
	                'phenotype_attribute' => $attr,
	                'phenotype_values' => $value);
				$this->createLocalPhenoTypeLookup($data);
            }
        }

        return $this->getLocalPhenotypes('phenotype_attribute, phenotype_values', ["network_key"=>$network_key]);
    }

    function getLocalPhenotypes(string $cols = null,array $conds = null){

        $this->builder = $this->db->table('local_phenotypes_lookup');
        if ($cols) {
            $this->builder->select($cols);
        }
        if($conds){
            $this->builder->where($conds);
        }
        $query = $this->builder->get()->getResultArray();

        return $query;
    }

    function updateLocalPhenoTypes(array $updateData,array $conds = null){
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