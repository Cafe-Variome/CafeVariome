<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine;

/**
 * Name Database.php
 *
 * Created 08/07/2021
 * @author Mehdi Mehtarizadeh
 * @author Farid Yavari Dizjikan
 *
 */

class Database
{
	private $db;
	public function __construct()
	{
		$this->db = $this->initiateDB();
	}

	private function loadConfig():array
	{
		return config('Database')->default;
	}

	private function initiateDB()
	{
		$db_config = $this->loadConfig();
		$db = new \mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database'], $db_config['port']);
		return $db;
	}

	public function insert(string $query_string)
	{
		$this->db->query($query_string);
	}

	public function begin_transaction()
	{
		$this->db->begin_transaction();
	}

	public function commit(bool $renew_db = true): bool
	{
		$transaction_status = $this->db->commit();

		if ($renew_db){
			unset($this->db);
			$this->db = $this->initiateDB();
		}
		return $transaction_status;
	}

}
