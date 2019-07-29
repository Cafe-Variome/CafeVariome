<?php namespace App\Models;

/**
 * Name Users.php
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


    public function __construct(ConnectionInterface &$db){

        $this->db =& $db;
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
    public function getUserByUsername($uname){

        $this->builder = $this->db->table($this->table);
        $this->builder->where('username', $uname);
        $query = $this->builder->get()->getResult();

        return ($query) ? $query : null;
    }

    /**
     * Gets all users in the installation.
     * @return array
     */

     public function getUsers(){
        $this->builder = $this->db->table($this->table);
        $query = $this->builder->get()->getResultArray();
        return ($query) ? $query : null;
     }
}