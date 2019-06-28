<?php  namespace App\Models;


//Moved to CI4 by Mehdi Mehtarizadeh(mm876) on 17/06/2019

/**
* Name:  CMS Model
*
* Author:  Owen Lancaster
* 		   ol8@leicester.ac.uk
*
*/

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;


class Cms_model extends Model {

	protected $db;
	protected $builder;

	protected $table      = 'menus';
	protected $primaryKey = 'menu_id';

	protected $tempReturnType = 'array';


	public function __construct(ConnectionInterface &$db)
	{
		$this->db =& $db;
		//$this->builder = $this->db->table($this->table);

	}
	
	/**
	 * This function populates menus from menus table.
	 * Records are ordered by menu_id.
	 *
	 * @return array
	 */
	public function getMenus() {
		$this->builder = $this->db->table('menus');
		$query = $this->builder->orderBy('menu_id', 'asc')->get()->getResultArray();
		return $query;
	}
	
	public function insertMenu($data) {
		$this->db->insert('menus', $data);
		$insert_id = $this->db->insert_id();
		return $insert_id;
	}

	public function getPages() {
		$query = $this->db->get('pages')->result_array();
		return $query;
	}

	public function getPage($page_name) {
		$query = $this->db->get_where('pages', array('page_name' => $page_name));
		$row = $query->row_array();
		return $row;
	}
	
	public function getPageByID($page_id) {
		$query = $this->db->get_where('pages', array('page_id' => $page_id));
		$row = $query->row_array();
		return $row;
	}

	/**
	 * 
	 * This function apparently runs the sql query below:
	 * <code>
	 * SELECT GROUP_CONCAT(page_name SEPARATOR ' | ') as page_names FROM `pages` WHERE `parent_menu` = '$menu_name' GROUP BY `parent_menu`
	 * </code>
	 * 
	 * @param string $menu_name : Goes in the Where clause.
	 * 
	 * @return string
	 */
	public function getPagesForMenu($menu_name) {

		$this->builder = $this->db->table('pages');

		$this->builder->select("GROUP_CONCAT(page_name SEPARATOR ' | ') as page_names");
		$this->builder->where('parent_menu', $menu_name);
		$this->builder->groupBy('parent_menu');
		
		$query = $this->builder->get()->getResultArray();

		if ( ! empty ($query)) {
			$page_names = $query[0]['page_names'];
		}
		else {
			$page_names = "No pages have been assigned";
		}
		
		return $page_names;
	}
	
	public function unlinkMenuFromPages($menu_name) {
		$data = array(
               'parent_menu' => ''
		);
		$this->db->where('parent_menu', $menu_name);
		$this->db->update('pages', $data); 
		error_log($this->db->last_query());
	}
	
	
	public function insertPage($data) {
		$this->db->insert('pages', $data);
		$insert_id = $this->db->insert_id();
		return $insert_id;
	}
	
	public function updatePage($data, $page_id) {
		$this->db->where('page_id', $page_id);
		$this->db->update('pages', $data);
//		error_log($this->db->last_query());
	}

	public function deletePage($page_id) {
		$this->db->delete('pages', array('page_id' => $page_id)); 
	}

	public function deleteMenu($menu_name) {
		$menu_name = urldecode($menu_name);
		$this->db->delete('menus', array('menu_name' => $menu_name));
//		error_log($this->db->last_query());
	}
	
	public function checkMenuExists($menu_name) {
		$query = $this->db->get_where('menus', array('menu_name' => $menu_name));
//		error_log($this->db->last_query());
		if ($query->num_rows() > 0){
			return true;
		}
		else {
			return false;
		}
	}

	public function checkMenuOnlyHasSinglePageAssigned($menu_name) {
		$query = $this->db->get_where('pages', array('parent_menu' => $menu_name));
//		error_log($this->db->last_query());
		if ($query->num_rows() > 0){
			return true;
		}
		else {
			return false;
		}
		
	}
	
	public function deleteMenus() {
//		$this->db->empty_table('display_fields');
		$this->db->truncate('menus');
	}
	
}
