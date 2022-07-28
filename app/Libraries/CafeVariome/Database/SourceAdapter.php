<?php namespace App\Libraries\CafeVariome\Database;

/**
 * SourceAdapter.php
 * Created 21/06/2022
 *
 * This class offers CRUD operation for Source.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\SourceFactory;

class SourceAdapter extends BaseAdapter
{
	/**
	 * @inheritDoc
	 */
	protected string $table = 'sources';

	/**
	 * @inheritDoc
	 */
	protected string $key = 'id';

	public function ReadAllOnline(): array
	{
		$this->builder->select();
		$this->builder->where('status', SOURCE_STATUS_ONLINE);
		$results = $this->builder->get()->getResult();

		$entities = [];
		for($c = 0; $c < count($results); $c++)
		{
			array_push($entities, $this->toEntity($results[$c]));
		}

		return $entities;
	}

	public function Lock(int $id): bool
	{
		$this->builder->where($this->key, $id);
		return $this->builder->update(['locked' => 1]);
	}

	public function Unlock(int $id): bool
	{
		$this->builder->where($this->key, $id);
		return $this->builder->update(['locked' => 0]);
	}

	/**
	 * @inheritDoc
	 */
	public function toEntity(?object $object): IEntity
	{
		$sourceFactory = new SourceFactory();
		return $sourceFactory->GetInstance($object);
	}
}
