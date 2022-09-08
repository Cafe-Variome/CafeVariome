<?php namespace App\Libraries\CafeVariome\Database;

/**
 * DiscoveryGroupAdapter.php
 * Created 06/09/2022
 *
 * This class offers CRUD operation for DiscoveryGroup.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\DiscoveryGroupFactory;

class DiscoveryGroupAdapter extends BaseAdapter
{
	/**
	 * @inheritDoc
	 */
	protected static string $table = 'discovery_groups';

	/**
	 * @inheritDoc
	 */
	protected static string $key = 'id';

	public function CreateUserAssociations(int $id, array $user_ids)
	{
		$this->changeTable('discovery_group_users');

		$data = [];

		for ($i = 0; $i < count($user_ids); $i++)
		{
			$data[] = ['user_id' => $user_ids[$i], 'discovery_group_id' => $id];
		}

		$this->builder->insertBatch($data);

		$this->resetTable();
	}

	public function CreateSourceAssociations(int $id, array $source_ids)
	{
		$this->changeTable('discovery_group_sources');

		$data = [];

		for ($i = 0; $i < count($source_ids); $i++)
		{
			$data[] = ['source_id' => $source_ids[$i], 'discovery_group_id' => $id];
		}

		$this->builder->insertBatch($data);

		$this->resetTable();
	}

	public function ReadAssociatedUserIds(array $ids): array
	{
		$this->changeTable('discovery_group_users');

		$this->builder->select('user_id');
		$this->builder->whereIn('discovery_group_id', $ids);

		$results = $this->builder->get()->getResult();

		$user_ids = [];
		for($c = 0; $c < count($results); $c++)
		{
			array_push($user_ids, $results[$c]->user_id);
		}

		$this->resetTable();

		return $user_ids;
	}

	public function ReadAssociatedSourceIds(array $ids): array
	{
		$this->changeTable('discovery_group_sources');

		$this->builder->select('source_id');
		$this->builder->whereIn('discovery_group_id', $ids);

		$results = $this->builder->get()->getResult();

		$source_ids = [];
		for($c = 0; $c < count($results); $c++)
		{
			array_push($source_ids, $results[$c]->source_id);
		}

		$this->resetTable();

		return $source_ids;
	}

	public function ReadAssociatedIdsAndSourceIds(array $ids): array
	{
		$this->changeTable('discovery_group_sources');

		$this->builder->select('discovery_group_id, source_id');
		$this->builder->whereIn('discovery_group_id', $ids);

		$results = $this->builder->get()->getResult();

		$ds_ids = [];
		for($c = 0; $c < count($results); $c++)
		{
			array_push($ds_ids, $results[$c]);
		}

		$this->resetTable();

		return $ds_ids;
	}

	public function ReadByNameAndNetworkId(string $name, int $network_id): IEntity
	{
		$this->builder->select();
		$this->builder->where(static::$table . '.name', $name);
		$this->builder->where(static::$table . '.network_id', $network_id);

		$results = $this->builder->get()->getResult();

		$record = null;
		if (count($results) == 1)
		{
			$record = $results[0];
		}

		return $this->toEntity($record);
	}

	public function ReadByNetworkId(int $network_id): array
	{
		$this->CompileSelect();
		$this->CompileJoin();
		$this->builder->where(static::$table . '.network_id', $network_id);

		$results = $this->builder->get()->getResult();

		$entities = [];
		for($c = 0; $c < count($results); $c++)
		{
			$entities[$results[$c]->{static::$key}] = $this->binding != null ? $this->BindTo($results[$c]) : $this->toEntity($results[$c]);
		}

		return $entities;
	}

	public function DeleteUserAssociations(int $id)
	{
		$this->changeTable('discovery_group_users');

		$this->builder->where('discovery_group_id', $id);
		$this->builder->delete();

		$this->resetTable();
	}

	public function DeleteSourceAssociations(int $id)
	{
		$this->changeTable('discovery_group_sources');

		$this->builder->where('discovery_group_id', $id);
		$this->builder->delete();

		$this->resetTable();
	}

    /**
     * @inheritDoc
     */
    public function toEntity(?object $object): IEntity
    {
        $discoveryGroupFactory = new DiscoveryGroupFactory();
		return $discoveryGroupFactory->GetInstance($object);
    }
}
