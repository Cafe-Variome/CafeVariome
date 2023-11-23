<?php namespace App\Libraries\CafeVariome\Database;

/**
 * SingleSignOnProviderAdapter.php
 * Created 22/05/2022
 *
 * This class offers CRUD operation for SingleSignOnProvider.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\ServerAdapterFactory;
use App\Libraries\CafeVariome\Factory\SingleSignOnProviderFactory;

class SingleSignOnProviderAdapter extends BaseAdapter
{
	/**
	 * @inheritDoc
	 */
	protected static string $table = 'single_sign_on_providers';

	/**
	 * @inheritDoc
	 */
	protected static string $key = 'id';

	public function ReadUserLoginSingleSignOnProviders(): array
	{
		$this->builder->select();
		$this->builder->where('user_login', true);
		$results = $this->builder->get()->getResult();

		$entities = [];
		for($c = 0; $c < count($results); $c++)
		{
			array_push($entities, $this->toEntity($results[$c]));
		}

		return $entities;
	}

	/**
	 * @param string $url
	 * @param bool $query
	 * @return IEntity
	 * @throws \Exception
	 */
	public function ReadByURL(string $url, bool $query = true): IEntity
	{
		$serverAdapter = (new ServerAdapterFactory())->GetInstance();
		$serverTable = $serverAdapter->GetTable();
		$serverKey = $serverAdapter->GetKey();
		$this->builder->select( $this->table . '.*');
		$this->builder->join($serverTable, $serverTable . '.' . $serverKey . '=' . $this->table . '.server_id');
		$this->builder->where($serverTable . '.address', $url);
		$this->builder->where($this->table . '.query', $query);
		$results = $this->builder->get()->getResult();

		$record = null;
		if (count($results) == 1)
		{
			$record = $results[0];
		}

		return $this->toEntity($record);
	}

	public function ReadIDbyURL(string $url): int
	{
		$serverAdapter = (new ServerAdapterFactory())->GetInstance();
		$serverTable = $serverAdapter->GetTable();
		$serverKey = $serverAdapter->GetKey();
		$this->builder->select(self::$table . '.id');
		$this->builder->join($serverTable, $serverTable . '.' . $serverKey . '=' . self::$table . '.server_id');
		$this->builder->where($serverTable . '.address', $url);
		$results = $this->builder->get()->getResult();

		return (int)$results;
	}

	/**
	 * Converts general PHP objects to a SingleSignOnProvider object.
	 * @param object|null $object
	 * @return IEntity
	 * @throws \Exception
	 */
    public function toEntity(?object $object): IEntity
    {
		$singleSignOnProviderFactory = new SingleSignOnProviderFactory();
		return $singleSignOnProviderFactory->GetInstance($object);
    }
}
