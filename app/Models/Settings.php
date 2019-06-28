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
	protected $primaryKey = 'settings_id';

	protected $tempReturnType = 'array';

    /**
     * Constructor
     * 
     * @param ConnectionInterface $db
     * @return object
     */

  public function __construct(ConnectionInterface &$db)
  {
      $this->db =& $db;
      $settings = $this->findAll();
      $c = 0;
      foreach ($settings as $row) {
        $c++;

        if ( $row['value'] == "off") {
                  $this->settingData[$row['name']] = false;
        }
        else {
              $this->settingData[$row['name']] =  $row['value'];               
        }

      }
  }

    public static function getInstance(ConnectionInterface &$db)
    {
      if (self::$_settings == null)
      {
        self::$_settings = new Settings($db);
      }
   
      return self::$_settings;
    }

}
?>