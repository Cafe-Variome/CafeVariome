<?php namespace App\Libraries\CafeVariome\Database;

/**
 * UserAdapter.php
 * Created 25/05/2022
 *
 * This class offers CRUD operation for User.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\UserFactory;

class UserAdapter extends BaseAdapter
{
	/**
	 * @inheritDoc
	 */
	protected string $table = 'users';

	/**
	 * @inheritDoc
	 */
	protected string $key = 'id';

	public function ReadIdByEmail(string $email): ?int
	{
		$this->builder->select('id');
		$this->builder->where('email', $email);
		$results = $this->builder->get()->getResult();

		if (count($results) == 1)
		{
			$record = $results[0];
			return $this->toEntity($record)->getID();
		}

		return null;
	}

	public function Read(int $id): IEntity
	{
		$this->builder->select('id, username, email, first_name, last_name, company, active, remote, phone, last_login, created_on, is_admin, ip_address');
		$this->builder->where($this->key, $id);
		$results = $this->builder->get()->getResult();

		$record = null;
		if (count($results) == 1)
		{
			$record = $results[0];
		}

		return $this->toEntity($record);
	}

	public function ReadAll(): array
	{
		$this->builder->select('id, email, last_login, first_name, last_name, company, active, is_admin');
		$results = $this->builder->get()->getResult();

		$entities = [];
		for($c = 0; $c < count($results); $c++)
		{
			array_push($entities, $this->toEntity($results[$c]));
		}

		return $entities;
	}

	public function UpdateLastLogin(int $id): bool
	{
		$this->builder->where($this->key, $id);
		return $this->builder->update(['last_login' => time()]);
	}

	/**
     * @inheritDoc
     */
    public function toEntity(?object $object): IEntity
    {
		$userFactory = new UserFactory();
		return $userFactory->getInstance($object);
    }
}
