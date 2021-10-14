<?php namespace App\Models;

/**
 * Name Source.php
 * @author Owen Lancaster
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 *
 * Source model class. This class handles operations on source entities.
 */

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;

 class Source extends Model{

    protected $db;
    protected $table      = 'sources';
    protected $builder;

    protected $primaryKey = 'source_id';

    public function __construct(ConnectionInterface &$db = null){
        if ($db != null) {
            $this->db =& $db;
        }
        else {
            $this->db = \Config\Database::connect();
        }
    }

    /**
     *
     */
    public function getOnlineSources() {
        $this->builder = $this->db->table($this->table);

        $this->builder->where('status', 'online');
        $query = $this->builder->get()->getResultArray();

        return $query;
    }

    public function createSource($data) {
        $this->builder = $this->db->table($this->table);
		$this->builder->insert($data);
		$insert_id = $this->db->insertID();
		return $insert_id;
    }

    /**
     *
     */
    public function updateSource(array $data, array $conds) {
        $this->builder = $this->db->table($this->table);
        if ($conds) {
            $this->builder->where($conds);
        }
        $this->builder->update($data);
    }

    /**
	 * getSources
     *
	 * General function to get fetch data from sources table.
     *
     * @author Mehdi Mehtarizadeh
	 */
	function getSources(string $cols = null, array $conds = null, array $groupby = null, bool $isDistinct = false, int $limit = -1, int $offset = -1){
		$this->builder = $this->db->table($this->table);

		if ($cols) {
            $this->builder->select($cols);
        }
        if ($conds) {
            $this->builder->where($conds);
        }
        if ($groupby) {
            $this->builder->groupBy($groupby);
        }
        if ($isDistinct) {
            $this->builder->distinct();
        }
        if ($limit > 0) {
            if ($offset > 0) {
                $this->builder->limit($limit, $offset);
            }
            $this->builder->limit($limit);
        }

        $query = $this->builder->get()->getResultArray();
        return $query;
    }

    /**
     *
     */
    public function getSource(int $source_id)
	{
        $this->builder = $this->db->table($this->table);
        $this->builder->where('source_id', $source_id);
        $query = $this->builder->get()->getResultArray();
		return count($query) == 1 ? $query[0] : null;
    }

    /**
     * getSourcesByNetwork(int $network_key)
     *
     * @return array
     */
    public function getSourcesByNetwork(int $network_key)
    {
		$this->builder = $this->db->table('network_groups_sources');
		$this->builder->select('source_id, network_key');
		$this->builder->where('network_key', $network_key);

        $data = $this->builder->get()->getResultArray();

        return $data;
    }

    /**
     * getSourcesByNetworks(array $network_keys)
     *
     * @return array
     */
    public function getSourcesByNetworks(array $network_keys)
    {
		$this->builder = $this->db->table('network_groups_sources');
        $this->builder->select('source_id, network_key');
        $this->builder->distinct();
		$this->builder->whereIn('network_key', $network_keys);

        $data = $this->builder->get()->getResultArray();

        return $data;
    }

    /**
     * @deprecated
     */
    function getSourceElasticStatus() {
        $this->builder = $this->db->table($this->table);

        $this->builder->select('source_id, name, elastic_status');
        $query = $this->builder->get()->getResultArray();

        return $query;
    }

    function getSpecificSources($source_ids){

        $this->builder = $this->db->table($this->table);
        $this->builder->whereIn('source_id',  $source_ids);
        $query = $this->builder->get()->getResultArray();

        return $query;
    }

    /**
     *
     *
     */
    function getSourceId($group_id) {
        $this->builder = $this->db->table('network_groups_sources');
        $this->builder->select('source_id');
        $this->builder->where('group_id', $group_id);

        $sources = $this->builder->get()->getResultArray();

		return $sources;
	}

    public function getSourceIDByName($source) {
        $this->builder = $this->db->table($this->table);
        $this->builder->select('source_id');
        $this->builder->where('name', $source);
        $query = $this->builder->get()->getResultArray();
        return $query? $query[0]['source_id'] : null;
    }

    /**
     * Get Source Name - Get the source name for given ID
     *
     * @param int $source_id - The source ID we are trying to find name for
     * @return string the Source Name
     *
     * Moved to source model by Mehdi Mehtarizadeh(02/08/2019)
     */
    public function getSourceNameByID(int $source_id)
	{
        $this->builder = $this->db->table($this->table);

        $this->builder->select('name');
        $this->builder->where('source_id', $source_id);
        $query = $this->builder->get()->getResultArray();
        return ($query) ? $query[0]['name'] : null;
    }

    /**
     * Is Source Locked - Is the given Source Locked?
     *
     * @param int $source_id - The source we are checking
     * @return int 0 if not locked | 1 if it is
     */
    public function isSourceLocked($source_id): bool {
        $this->builder = $this->db->table($this->table);

        $this->builder->select('elastic_lock');
        $this->builder->where('source_id', $source_id);
        $query = $this->builder->get()->getResultArray();
        return $query ? $query[0]['elastic_lock'] : false;
    }

    /**
     * Counts entries per source.
     */
    function countSourceEntries(int $source_id = -1) {

        $this->builder = $this->db->table('eavs use index(subj_src)');
        $this->builder->select('COUNT(distinct(subject_id)) as total, eavs.source_id');
        $this->builder->join('sources', 'eavs.source_id = sources.source_id', 'right');
        if ($source_id > 0) {
            $this->builder->where('eavs.source_id', $source_id);
        }
        $this->builder->groupBy('sources.source_id');
        $query = $this->builder->get()->getResultArray();
        $source_counts = array();
        if ($source_id > 0) {
            return count($query) == 1 ? $query[0]['total'] : 0;
        }
        foreach ($query as $r) {
            $source_counts[$r["source_id"]] = $r["total"];
        }
        return $source_counts;
    }

    /**
     *
     */
    public function deleteSourceFromEAVs(int $source_id) {
        $this->builder = $this->db->table('eavs');
        $this->builder->delete(['source_id' => $source_id]);
    }

    /**
     *
     */
    public function deleteSource($source_id) {
        $this->builder = $this->db->table($this->table);
        $this->builder->delete(['source_id' => $source_id]);
    }

	 /**
	  * @deprecated
	  */
    private function canCurateSource($source_id, $user_id) {
        $this->builder = $this->db->table('curators');

        $where = "source_id = '$source_id' AND user_id = '$user_id'";
        $this->builder->where($where);
        $query = $this->builder->get()->getResultArray();
        $count = $this->builder->countAllResults();
        return $count;
    }

    /**
     * Toggle Source Lock -  Lock the source which is currently being regenerated or
     * uploaded to
     *
     * Moved to source model by Mehdi Mehtarizadeh (02/08/2019)
     * @deprecated
     * @param int $source_id - The source we are locking
     * @return N/A
     */
    public function toggleSourceLock($source_id) {
        $this->builder = $this->db->table($this->table);

        $data = array(
            "elastic_lock" => "!elastic_lock"
        );
        $this->builder->where('source_id', $source_id);
        $this->builder->update($data);
    }

    /**
     * Source Lock -  Lock the source which is currently being regenerated or
     * uploaded to
     *
     * Moved to source model by Mehdi Mehtarizadeh (02/08/2019)
     *
     * @param int $source_id - The source we are locking
     * @return void
     */
    public function lockSource(int $source_id)
    {
        $this->builder = $this->db->table($this->table);

        $data = ["elastic_lock" => "1"];
        $this->builder->where('source_id', $source_id);
        $this->builder->update($data);
    }

    /**
     * Source Unock -  Unlock the source which was being regenerated or
     * uploaded to
     *
     * Moved to source model by Mehdi Mehtarizadeh (02/08/2019)
     *
     * @param int $source_id - The source we are locking
     * @return void
     */
    public function unlockSource(int $source_id)
    {
        $this->builder = $this->db->table($this->table);

        $data = ["elastic_lock" => "0"];
        $this->builder->where('source_id', $source_id);
        $this->builder->update($data);
    }

    function getSourcesByUserIdAndNetworkKey(int $user_id, string $installation_key, int $network_key, string $accesstype = 'master')
	{
        $this->builder = $this->db->table('users_groups_networks');

        $this->builder->select('ngs.source_id');
        $this->builder->join('network_groups_sources as ngs', 'ngs.group_id = users_groups_networks.group_id');
        $this->builder->join('network_groups as ng', 'ng.id = users_groups_networks.group_id');
        $this->builder->where(array(
                            'users_groups_networks.user_id' => $user_id,
                            'ngs.installation_key' => $installation_key,
                            'ng.group_type' => $accesstype,
                            'ngs.network_key' => $network_key
                ));
        $query = $this->builder->get()->getResultArray();
        return $query;
    }

    /**
     * setElasticFlag
     * Set elastic_status flag to 1 for a source.
     *
     * @param int $source_id - The name of the source
     * @return N/A
     */
    function setElasticFlag(int $source_id)
    {
        $this->builder = $this->db->table($this->table);

        $data = array('elastic_status' => 1);
        $this->builder->where('source_id', $source_id);
        $this->builder->update($data);
    }

    /**
     * getElasticFlag
     * get elastic_status for a source from sources table
     *
     * @param int $source_id - The name of the source
     * @return array $query       - All columns for all files which are fresh
     */
    function getElasticFlag(int $source_id):int
    {
        $this->builder = $this->db->table($this->table);

        $this->builder->select('elastic_status');
        $this->builder->where('source_id', $source_id);

        $query = $this->builder->get()->getResult();

        return ($query) ? $query[0]->elastic_status : -1;
    }

 }
