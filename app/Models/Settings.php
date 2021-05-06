<?php namespace App\Models;

/**
 * Settings.php
 * @author: Mehdi Mehtarizadeh
 * Created: 18/06/2019
 * Model class that contains CafeVariome settings from database.
 * Data is loaded from 'settings' table.
 * This class follows a singleton pattern.
 */

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;

class Settings extends Model{

	protected $db;

  private static $_settings = null;
    
  public $settingData = Array();

	protected $table      = 'settings';
	protected $primaryKey = 'setting_id';

	protected $tempReturnType = 'array';

    /**
     * Constructor
     * 
     * @param ConnectionInterface $db
     * @return object
     */

    private function __construct(ConnectionInterface &$db = null, bool $returnFull = false)
    {
          if ($db != null) {
            $this->db =& $db;
          }
          else {
              $this->db = \Config\Database::connect();
          }
          if (!$returnFull) {    
          $settings = $this->findAll();
          $c = 0;
          foreach ($settings as $row) {
            $c++;

            if ( $row['value'] == "off") {
                $this->settingData[$row['setting_key']] = false;
            }
            else {
                $this->settingData[$row['setting_key']] =  $row['value'];               
            }
          }
      }
    }

    public static function getInstance()
    {
      if (self::$_settings == null)
      {
        self::$_settings = new Settings();
      }
   
      return self::$_settings;
    }

    public function getSettings(string $cols = null, array $conds = null, int $limit = -1, int $offset = -1){
        $builder = $this->db->table($this->table);		

        if ($cols) {
            $builder->select($cols);
        }
        if ($conds) {
            $builder->where($conds);
        }
        if ($limit > 0) {
            if ($offset > 0) {
                $builder->limit($limit, $offset);
            }
            $builder->limit($limit);
        }

        $query = $builder->get()->getResultArray();
        return $query; 
    }

    public function getSettingsByGroup(string $group)
    {
        return $this->getSettings(null, ['setting_group' => $group]);
    }

    public function updateSettings(array $data, array $conds) {
      $this->builder = $this->db->table($this->table);
      if ($conds) {
          $this->builder->where($conds);
      }
      $this->builder->update($data);
    }

    public function getInstallationKey()
    {
        return $this->settingData['installation_key'];
    }

    public function getAuthServerUrl()
    {
        return $this->settingData['auth_server'];
    }

    public function getElasticSearchUri()
    { 
        return $this->settingData['elastic_url'];
    }

    public function getNeo4JUserName()
    {
        return $this->settingData['neo4j_username'];
    }

    public function getNeo4JPassword()
    {
        return $this->settingData['neo4j_password'];
    }

    public function getNeo4JUri()
    {
        return $this->settingData['neo4j_server'];
    }

    public function getNeo4JPort()
    {
        return $this->settingData['neo4j_port'];
    }

    public function getOpenIDEndpoint()
    {
        return $this->settingData["oidc_uri"];
    }

    public function getOpenIDPort()
    {
        return $this->settingData["oidc_port"];
    }

    public function getOpenIDRealm()
    {
        return $this->settingData["oidc_realm"];
    }

    public function getOpenIDClientId()
    {
        return $this->settingData["oidc_client_id"];
    }

    public function getOpenIDClientSecret()
    {
        return $this->settingData["oidc_client_secret"];
    }

    /**
     * @deprecated
     */
    public function getOpenIDRedirectUri()
    {
        return $this->settingData["oidc_login_uri"];
    }
}
