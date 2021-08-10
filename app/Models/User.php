<?php namespace App\Models;

/**
 * Name Users.php
 * @author Owen Lancaster
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 *
 * User model class that handles operations on User entity.
 */

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;


class User extends Model{

	protected $db;
    protected $table      = 'users';
    protected $builder;

    protected $primaryKey = 'id';

   // protected $tempReturnType = 'array';


    public function __construct(ConnectionInterface &$db = null){
        if ($db != null) {
            $this->db =& $db;
        }
        else {
            $this->db = \Config\Database::connect();
        }
    }

    function createUser(string $email, string $username, &$authadapter, array $groups = [], array $data = []){
        $authadapter->register($email, $username, $data, $groups);
    }

    function updateUser(int $user_id, &$authadapter, array $groups = [], array $data = []){
        $authadapter->update($user_id, $data, $groups);
    }

    function deleteUser(int $user_id, &$authadapter){
        $authadapter->delete($user_id);
    }
function detailsUser(int $user_id, &$authadapter){
        $authadapter->details($user_id);
    }
    /**
     * Gets user object by Id.
     * @param int id User ID
     * @return object|null
     */
    public function getUserById($id){

        $this->builder = $this->db->table($this->table);
        $this->builder->where('id', $id);
        $query = $this->builder->get()->getResult();

        return ($query) ? $query : null;
    }

    /**
     * Gets user object by Email.
     * @param string email User Email
     * @return object|null
     */
    public function getUserByEmail($email){

        $this->builder = $this->db->table($this->table);
        $this->builder->where('email', $email);
        $query = $this->builder->get()->getResult();

        return ($query) ? $query : null;
    }

    /**
     * Gets user object by Username.
     * @param string uname User Username
     * @return object|null
     */
    public function getUserByUsername(string $uname){

        $this->builder = $this->db->table($this->table);
        $this->builder->where('username', $uname);
        $query = $this->builder->get()->getResult();

        return ($query) ? $query[0] : null;
    }
    /**
	 * getUsers
     *
	 * General function to get fetch data from users table.
     *
     * @author Mehdi Mehtarizadeh
	 */
	function getUsers(string $cols = null, array $conds = null, array $groupby = null, bool $isDistinct = false, int $limit = -1, int $offset = -1){
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
     * userExists
     * returns true if user exists in the local database.
     *
     * @author Mehdi Mehtarizadeh
     * @param string $username
     * @return bool true if user exists false otherwise
     */
    function userExists(string $username):bool{
        if ($this->getUserByUsername($username)) {
            return true;
        }
        return false;
    }

    /**
     * getName
     * @param int $id
     * @param bool $fullName
     * @return string user's first (and last name) or username if both first and last name are null
     */
    public function getName(int $id, bool $fullName = false): string
    {
        $user = $this->getUsers('username, first_name, last_name', ["id" => $id]);
        $name = '';
        if (count($user) == 1) {
            if ($user[0]['first_name'] == null && $user[0]['last_name']) {
                $name = $user[0]['username'];
            }
            else {
                if ($fullName) {
                    $name = $user[0]['first_name'] . ' ' . $user[0]['last_name'];
                }
                else {
                    $name = $user[0]['first_name'];
                }
            }
        }
        return $name;
    }

    /**
     * Add Remote User - Add a  minimal new user as remote
     *
     * @param string $email   - The email of the new user
	 * @return int $insert_id ID of inserted user
     */
	function createRemoteUser($email) {
		$data = array(
            'email'  	=> $email,
            'username'  => $email,
            'remote'  	=> 1,
            'active'    => 0);
        $this->builder = $this->db->table($this->table);
        $this->builder->insert($data);
		$insert_id = $this->db->insertID();
		return $insert_id;
	}
}
