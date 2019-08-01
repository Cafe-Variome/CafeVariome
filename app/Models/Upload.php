<?php namespace App\Models;

/**
 * Name Upload.php
 * Created 01/08/2019
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 * 
 * Upload model class that handles operations on data files.
 */

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;

 class Upload extends Model 
 {
    protected $db;
    protected $table      = 'uploaddatastatus';
    protected $builder;

    protected $primaryKey = 'id';

    public function __construct(ConnectionInterface &$db){

        $this->db =& $db;
    }


    /**
     * Check Json Files - Check if any of the Json files already exist on the server for
     * given source
     *
     * @param array $files   - The list of files to check
     * @param int $source_id - The source_id we are checking
     * @return array empty if no duplicates | with elements of file names if they exist
     */
    public function checkJsonFiles($files,$source_id) {
        // create array
        $duplicates = [];
        // loop through files array
        for ($i=0; $i < count($files); $i++) { 
            $this->builder = $this->db->table($this->table);
            $this->builder->where('source_id', $source_id);
            $this->builder->where('FileName', $files[$i]);
            $count = $this->builder->countAllResults();

            // if the count is greater than 1 push it into duplicates array
            if ($count != 0) {
                array_push($duplicates, $files[$i]);
            }
        }
        return $duplicates;
    }
 }
 