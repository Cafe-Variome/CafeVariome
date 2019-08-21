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

    protected $primaryKey = 'network_id';
	
	public function __construct(ConnectionInterface &$db){

		$this->db =& $db;
		$this->setting =  Settings::getInstance($this->db);

	}

	/**
	 * Retrieve all networks
	 */
	function getNetworks(){
		$this->builder = $this->db->table('networks');
		$query = $this->builder->get()->getResultArray();

		return $query;
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

	function getNetworkSourcesForCurrentInstallation() {
		$net_keys = $this->getNetworkKeys();
		$this->builder = $this->db->table('network_groups_sources');

		$this->builder->select("network_key, source_id");
		$this->builder->distinct();
		$this->builder->whereIn("network_key", $net_keys);
		$query = $this->builder->get()->getResultArray();
	   	return $query;
	}
	
	function getNetworkGroupsForInstallation() {
		$this->builder = $this->db->table('network_groups');
		$this->builder->join('networks', 'network_groups.network_key = networks.network_key');

		$this->builder->groupBy('network_groups.name');
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



	
	function addUserToNetworkGroup($user_id, $group_id, $installation_key) {

		$this->builder = $this->db->table('network_groups');
		$this->builder->select("network_key");
		$this->builder->where('id', $group_id);
		$query = $this->builder->get()->getResultArray();
		$network_key = $query[0]['network_key'];
		$data = array ( 'group_id' => $group_id,
						'user_id' => $user_id,
						'installation_key' => $installation_key,
						'network_key' => $network_key,
		);

		$this->builder = $this->db->table('users_groups_networks');				
		$this->builder->insert( $data);
		//$insert_id = $this->db->insertID();
		return $network_key;
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

	function deleteSourceFromInstallationFromAllNetworkGroups($source_id) {
		$this->builder = $this->db->table('network_groups_sources');
		$this->builder->where('source_id',$source_id);
		$this->builder->delete();
	}

	function addSourceFromInstallationToNetworkGroup($data) {
		$this->builder = $this->db->table('network_groups_sources');
		$this->builder->insert('network_groups_sources', $data);
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
		$this->deleteSourceFromInstallationFromAllNetworkGroups($source_id, $installation_key);
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

	function addSourceToNetworkGroup(int $source_id, int $group_id, string $installation_key) {
		$networkGroupModel = new NetworkGroup($this->db);
		$network_key = $networkGroupModel->getNetworkGroups("network_key", ['id' => $group_id]);
		$this->builder = $this->db->table('network_groups_sources');
		$this->builder->insert(array('source_id' => $source_id, 'group_id' => $group_id, 'installation_key' => $installation_key, 'network_key' => $network_key[0]['network_key']));
	}
}