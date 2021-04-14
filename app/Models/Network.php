<?php namespace App\Models;

/**
* Name:  Network.php
* Created: 18/07/2019
*
* @author Owen Lancaster
* @author Gregory Warren
* @author Mehdi Mehtarizadeh
*
*
*/

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;

class Network extends Model{

	protected $db;
    protected $table      = 'networks';
    protected $builder;

    protected $primaryKey = 'network_key';

	private $setting;
	private $networkGroupModel;
	
	public function __construct(ConnectionInterface &$db = Null){

        if ($db != null) {
            $this->db =& $db;
        }
        else {
            $this->db = \Config\Database::connect();
		}

		$this->setting =  Settings::getInstance();
		$this->networkGroupModel = new NetworkGroup();
	}

	/**
	 * Retrieve all networks
	 */
	function getNetworks(){
		$this->builder = $this->db->table($this->table);
		$query = $this->builder->get()->getResultArray();

		return $query;
	}

	public function deleteNetwork(int $network_key)
	{
		$this->builder = $this->db->table($this->table);
		$this->builder->where(['network_key' => $network_key]);
		$this->builder->delete();
	}

	/**
	 * Retrieve all network keys
	 */
	function getNetworkKeys(){
		$this->builder = $this->db->table('networks');
		$this->builder->select('network_key');

		$query = $this->builder->get()->getResultArray();
		$network_keys = [];
		foreach ($query as $network_key ){
			array_push($network_keys, $network_key["network_key"]);
		}

		return $network_keys;
	}

	/**
	 * 
	 */
	function getMasterGroups() {
		$this->builder = $this->db->table('network_groups');
		$this->builder->select('id as group_id, name');
		$this->builder->where(array('group_type' => 'master'));
		$data = $this->builder->get()->getResultArray();
		$end = [];
		foreach ($data as $datum) {
			$end[$datum['name']] = $datum['group_id'];
		}
		return $end;
	}
	
	/**
	 * 
	 */
	public function createNetwork ($data) {
		$this->builder = $this->db->table($this->table);
		$this->builder->insert($data);
		$insert_id = $this->db->insertID();
		return $insert_id;
	}

	/**
	 * getNetworksBySource(int $source_id)
	 * 
	 * Returns network keys a source is assigned to.
	 * 
	 * @author Mehdi
	 * @param int $source_id
	 * @return array network keys|empty
	 */
	public function getNetworksBySource(int $source_id): array
	{
		$this->builder = $this->db->table('network_groups_sources');
		$this->builder->select('network_key');
		$this->builder->distinct();
		$this->builder->where('source_id', $source_id);

		$data = $this->builder->get()->getResultArray();

		$keys = [];

		foreach ($data as $record) {
			array_push($keys, $record['network_key']);
		}

		return $keys;
	}

    /**
     * 
     */
    function isUserMemberOfMasterNetworkGroupForNetwork($user_id, $network_key) {

		$this->db->select('ugn.user_id');
		$this->db->from('users_groups_networks as ugn');
		$this->db->join('network_groups as ng', 'ng.id = ugn.group_id');
		$this->db->where(array(
							'ugn.user_id' => $user_id,
							'ng.network_key' => $network_key,
							'ng.group_type' => 'master'
				));
		
		
		$num_results = $this->db->count_all_results();
		
		if ($num_results){

			return true;
		}
		else {
			return false;
		}
	}

	/**
     * Get Current Network Groups For Source In Installation - Get all Network Groups assigned to a Source
     *
     * @param int $source_id - The ID of the source in question 
	 * @return array         - Details of the Network Groups
     */
	function getCurrentNetworkGroupsForSourceInInstallation($source_id) {	
		$this->builder = $this->db->table('network_groups_sources');

		$this->builder->join('network_groups', 'network_groups.id = network_groups_sources.group_id');
		$this->builder->join('networks', 'networks.network_key = network_groups.network_key');
		$this->builder->where('source_id', $source_id);
		$query = $this->builder->get()->getResultArray();
		return $query;
	}

	function getNetworkSourcesForCurrentInstallation(int $source_id = -1) {
		$net_keys = $this->getNetworkKeys();
		$this->builder = $this->db->table('network_groups_sources');

		$this->builder->select("network_key, source_id");
		$this->builder->distinct();
		if (count($net_keys) > 0) {
			$this->builder->whereIn("network_key", $net_keys);
		}
		if ($source_id != -1) {
			$this->builder->where('source_id', $source_id);
		}
		$query = $this->builder->get()->getResultArray();
	   	return $query;
	}
	
	function getNetworkGroupsForInstallation() {
		$this->builder = $this->db->table('network_groups');
		$this->builder->join('networks', 'network_groups.network_key = networks.network_key');

		$this->builder->groupBy('network_groups.name, network_groups.id');
		$query = $this->builder->get()->getResultArray();
		return $query;
	}

	function countSourcesForNetworkGroup($group_id) {
		$this->builder = $this->db->table('network_groups_sources');

		$this->builder->where('group_id',$group_id);
		$num_results = $this->builder->countAllResults();
		return $num_results;
	}

	/**
     * Get Users For Network Group - Get a basic list of all users Within a Specific Network Group
     *
     * @param int $group_id - The ID of the Group
	 * @return array $query - List of Users
     */
	function getNetworkGroupUsers($group_id) {
		$this->builder = $this->db->table('users');
		$this->builder->select("users.id, users.username, users.remote");
		$this->builder->join('users_groups_networks', 'users_groups_networks.user_id=users.id');
		$this->builder->where(array('users_groups_networks.group_id' => $group_id));	
		$query = $this->builder->get()->getResultArray();
		return $query;
	}

	function getNetworkGroup($group_id){
		$this->builder = $this->db->table('network_groups');
		$this->builder->where(array('id' => $group_id));
		$query = $this->builder->get()->getResultArray();
		return  $query ? $query[0] : null;
	}


	function getNetworkKeybyGroupId(int $group_id){
		$this->builder = $this->db->table('network_groups');
		$this->builder->select("network_key");
		$this->builder->where('id', $group_id);
		$query = $this->builder->get()->getResultArray();
		$network_key = $query[0]['network_key'];

		return $network_key;
	}
	
	function addUserToNetworkGroup(int $user_id, int $group_id, string $installation_key, string $network_key) {
		$data = array ( 'group_id' => $group_id,
						'user_id' => $user_id,
						'installation_key' => $installation_key,
						'network_key' => $network_key,
		);

		$this->builder = $this->db->table('users_groups_networks');				
		$this->builder->insert( $data);
		$insert_id = $this->db->insertID();
		return $insert_id;
	}

	function deleteUserFromNetworkGroup(int $user_id, int $group_id, string $installation_key, string $network_key){
		$this->builder = $this->db->table('users_groups_networks');				
		$this->builder->where('user_id', $user_id);
		$this->builder->where('group_id', $group_id);
		$this->builder->where('installation_key', $installation_key);
		$this->builder->where('network_key', $network_key);

		$this->builder->delete();

	}

	function deleteUserFromAllNetworkGroups(int $user_id){
		$this->builder = $this->db->table('users_groups_networks');				
		$this->builder->where('user_id', $user_id);
		$this->builder->delete();
	}
	
	function deleteAllUsersFromNetworkGroup($group_id, $isMaster = false) {
		if($isMaster) {
			$this->builder = $this->db->table('network_groups');
			$this->builder->select("network_key");
			$this->builder->where('id', $group_id);
			$result = $this->builder->get()->getResultArray();
			$network_key = $result[0]['network_key'];

			//delete
			$this->builder = $this->db->table('users_groups_networks');
			$this->builder->where('network_key', $network_key);
			$this->builder->delete();
		} else {
			$this->builder = $this->db->table('users_groups_networks');
			$this->builder->where('group_id', $group_id);	
			$this->builder->delete();
		}
	}

	function deleteUserFromAllOtherNetworkGroups($network_key, $users) {
		$this->builder = $this->db->table('users_groups_networks');
		$this->builder->where('network_key', $network_key);	
		$this->builder->whereNotIn('user_id', $users);
		$this->builder->delete();
	}

	function deleteAllSourcesFromNetworkGroup($group_id, $installation_key) {
		$this->builder = $this->db->table('network_groups_sources');
		$this->builder->where('group_id', $group_id);	
		$this->builder->where('installation_key', $installation_key);	
		$this->builder->delete();
	}

	/**
	 * @deprecated
	 */
	function get_network_groups_for_installation() {

		$network_groups_for_installation = array();
		$installation_key = $this->setting->settingData['installation_key'];
		$url = base_url();
		foreach ( $this->getNetworkGroupsForInstallation() as $network_group ) {
			$number_sources = $this->countSourcesForNetworkGroup($network_group['id']);
			$network_group['number_of_sources'] = $number_sources;
			$network_groups_for_installation[] = $network_group;
		}
		if ( ! empty($network_groups_for_installation) ) {
			 return $network_groups_for_installation;
		}
		else {
			return array("error" => "No network groups are available for this installation");
		}
	}	

	function deleteSourceFromNetworkGroups(int $source_id, bool $delete_master = false) {

		$masterNetworkGroupIds = $this->networkGroupModel->getMasterNetworkGroupsBySourceId($source_id);
		
		$this->builder = $this->db->table('network_groups_sources');

		if (!$delete_master) {
			$this->builder->whereNotIn('group_id', $masterNetworkGroupIds);

		}

		$this->builder->where('source_id', $source_id);

		$this->builder->delete();
	}

	function addSourceFromInstallationToNetworkGroup($data) {
		$this->builder = $this->db->table('network_groups_sources');
		$this->builder->insert($data);
		$insert_id = $this->db->insertID();
		return $insert_id;
	}

	function addSourceFromInstallationToMultipleNetworkGroups($source_id,$groups_exploded) {
		$installation_key = $this->setting->settingData['installation_key'];
		
		$return_flag = 1;
		foreach ( $groups_exploded as $group_data ) {
			$groups_exploded = explode(',', $group_data);
			$group_id = $groups_exploded[0];
			$network_key = $groups_exploded[1];		
			$data = array ( 'group_id' => $group_id,
							'source_id' => $source_id,
							'installation_key' => $installation_key,
							'network_key' => $network_key,
						);
			$id = $this->addSourceFromInstallationToNetworkGroup($data);
			if (!$id) {
				$return_flag = 0;
			}
		}
		if ($return_flag) {
			return $return_flag;
		}
		else {
			return array("error" => "Could not add source to all (or any) network groups");
		}
	}

	function modify_current_network_groups_for_source_in_installation($source_id,$group_post_data) {
		$installation_key = $this->setting->settingData['installation_key'];
		$this->deleteSourceFromNetworkGroups($source_id);
		if($group_post_data) {
			foreach ( explode('|', $group_post_data) as $group ) {

				$group_exploded = explode(',', $group);
				$group_id = $group_exploded[0];
				$network_key = $group_exploded[1];

				$data = array ( 
						'group_id' => $group_id,
						'source_id' => $source_id,
						'installation_key' => $installation_key,
						'network_key' => $network_key,
					);

					$id = $this->addSourceFromInstallationToNetworkGroup($data);
			}
		} 
	}

	function getAllNetworksSourcesBySourceId(int $source_id) {
		$this->builder = $this->db->table('network_groups_sources');

		$this->builder->select('network_key');
		$this->builder->distinct();
		$this->builder->where('source_id', $source_id);
		$data = $this->builder->get()->getResultArray();
		$output = [];
		foreach ($data as $datum) {
			$this->builder = $this->db->table('network_groups_sources');

			$output[$datum['network_key']] = [];
			$this->builder->select('source_id');
			$this->builder->where('network_key', $datum['network_key']);
			$sources = $this->builder->get()->getResultArray();
			foreach ($sources as $source_id) {
				array_push($output[$datum['network_key']], $source_id['source_id']);
			}
		}
		return $output;
		// select distinct network_key from network_groups_sources  where source_id=9;

		// select source_id from network_groups_sources where network_key="a846f6d38152843bee11a38a82ebafbe";
	}

	/**
	 * @deprecated
	 */
    function checkIfGroupExistsInNetwork(string $network_key, string $group_name) {
        $query = $this->db->get_where('network_groups',array('network_key' => $network_key, 'name' => $group_name));
		if ($query->num_rows() > 0) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * addSourceToNetworkGroup
	 * @param int $source_id
	 * @param int $group_id
	 * @param string $installation_key
	 * 
	 * @author Mehdi Mehtarizadeh
	 */
	function addSourceToNetworkGroup(int $source_id, int $group_id, string $installation_key) {
		$networkGroupModel = new NetworkGroup($this->db);
		$network_key = $networkGroupModel->getNetworkGroups("network_key", ['id' => $group_id]);
		$this->builder = $this->db->table('network_groups_sources');
		$this->builder->insert(array('source_id' => $source_id, 'group_id' => $group_id, 'installation_key' => $installation_key, 'network_key' => $network_key[0]['network_key']));
	}

	/**
	 * getSourcesForNetworkPartOfGroup
	 * 
	 * @param string $installation_key
	 * @param string $network_key
	 * 
	 * @author Mehdi Mehtarizadeh
	 */
	function getSourcesForNetworkPartOfGroup(string $installation_key, string $network_key) {

		$this->builder = $this->db->table('network_groups_sources s');
		$this->builder->select("s.source_id, s.group_id");
		$this->builder->join("network_groups g", "g.id = s.group_id");
		$this->builder->where("g.network_key" ,$network_key);
		$this->builder->where("s.installation_key", $installation_key);
		
		$data = $this->builder->get()->getResultArray();
		$sources = array();

		foreach ($data as $value) {
			if(!in_array($value['source_id'], $sources))
				$sources[] = $value['source_id'];
		}

		return $sources;
	}

	/**
	 * getSourcesForNetworkPartOfGroup
	 * 
	 * @param int $user_id
	 * @return array|null
	 * @author Mehdi Mehtarizadeh
	 */
	public function getNetworksUserMemberOf(int $user_id) {
		$this->builder = $this->db->table('users_groups_networks');
		$this->builder->select("group_id");
		$this->builder->where("user_id", $user_id);
		$query = $this->builder->get()->getResultArray();

		if (!$query) {
			return $query;
		}
		
		$groupIds = array();
		foreach ($query as $gid) {
			array_push($groupIds, $gid['group_id']);
		}

		$this->builder = $this->db->table('network_groups');
		$this->builder->select("name, network_key");
		$this->builder->whereIn("id", $groupIds);
		$this->builder->where("group_type", "master");

		$query = $this->builder->get()->getResultArray();
		return $query;
	}

	/**
     * Get Current Network Groups For Users - Get a list of Administrator-made Network Groups
     *
     * @param N/A
	 * @return array $query - Array containing varius details about Network Groups
     */
	function getCurrentNetworkGroupsForUsers() {
		$this->builder = $this->db->table('users_groups_networks');
		$this->builder->join('network_groups', 'network_groups.id = users_groups_networks.group_id');
		$this->builder->join('networks', 'networks.network_key = users_groups_networks.network_key');
		$query = $this->builder->get()->getResultArray();
		if (!$query) {
			$query = ['error' => 'Unable to get current networks for sources in this installation'];
		}
		return $query;
	}

	function getNetworkGroupsForInstallationForUser(int $user_id) {
		$this->builder = $this->db->table('users_groups_networks');
		$this->builder->select('users_groups_networks.group_id, network_groups.network_key');
		$this->builder->join('network_groups', 'network_groups.id = users_groups_networks.group_id');
		$this->builder->join('networks', 'networks.network_key = users_groups_networks.network_key');
		$this->builder->where('user_id', $user_id);
		$query = $this->builder->get()->getResultArray();
		
		return $query;
	}

	/**
	 * New methods added for HDR Sprint 
	 * @author Gregory Warren
	 */

	function removeInstallations(array $installation_keys, int $network_key) {
		$this->builder = $this->db->table('installation_network_sums');

    	$this->builder->where('network_key', $network_key);
    	$this->builder->whereNotIn('installation_key', $installation_keys);
		$this->builder->delete();
    }

    function addInstallations(array $installation_keys, int $network_key) {
		$this->builder = $this->db->table('installation_network_sums');

		$data = [];
    	foreach ($installation_keys as $ikey) {
			array_push($data, ['network_key' => $network_key, 'installation_key' => $ikey]);
		}
		//$this->builder->ignore(true);
		$this->builder->insertBatch($data);
	}

	function getOldChecksums(int $network_key) {
		$this->builder = $this->db->table('installation_network_sums');

		$this->builder->select('installation_key,values_checksum');
		$this->builder->where('network_key',$network_key);
		$data = $this->builder->get()->getResultArray();
		$output = [];
		foreach ($data as $datum) {
			$output[$datum['installation_key']] = $datum['values_checksum'];
		}
		return $output;
	}

	function getOldHPOSums(int $network_key) {
		$this->builder = $this->db->table('installation_network_sums');

		$this->builder->select('installation_key,hpo_checksum');
		$this->builder->where('network_key', $network_key);
		$data = $this->builder->get()->getResultArray();
		$output = [];
		foreach ($data as $datum) {
			$output[$datum['installation_key']] = $datum['hpo_checksum'];
		}
		return $output;
	}

	function updateChecksum(string $checksum, int $network_key, string $installation_key, bool $hpo = false) {
		$this->builder = $this->db->table('installation_network_sums');

		if ($hpo) {
			$data = array(
		        'hpo_checksum' => $checksum
			);
		}
		else {
			$data = array(
		        'values_checksum' => $checksum
			);
		}
		$this->builder->where('network_key', $network_key);
		$this->builder->where('installation_key', $installation_key);
		$this->builder->update($data);
	}
}