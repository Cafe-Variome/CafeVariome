<?php namespace App\Libraries\CafeVariome\Database;

/**
 * DataFileAdapter.php
 * Created 17/05/2022
 *
 * This class offers CRUD operation for DataFile.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\DataFileFactory;
use App\Libraries\CafeVariome\Factory\UserAdapterFactory;

class DataFileAdapter extends BaseAdapter
{
	/**
	 * @inheritDoc
	 */
	protected string $table = 'data_files';

	/**
	 * @inheritDoc
	 */
	protected string $key = 'id';

	/**
	 * Converts general PHP objects to a DataFile object.
	 * @param object|null $object
	 * @return IEntity
	 * @throws \Exception
	 */
    public function toEntity(?object $object): IEntity
    {
        $dataFileFactory = new DataFileFactory();
		return $dataFileFactory->GetInstance($object);
    }

	public function ReadBySourceId(int $source_id): array
	{
		$userAdapter = (new UserAdapterFactory())->GetInstance();
		$this->builder->select($this->table . '.*, ' . $userAdapter->GetTable() . '.username, ' . $userAdapter->GetTable() . '.first_name, ' . $userAdapter->GetTable() . '.last_name');
		$this->builder->join($userAdapter->GetTable(), $this->table . '.user_id = ' . $userAdapter->GetTable() . '.' . $userAdapter->GetKey());
		$this->builder->where('source_id', $source_id);
		$results = $this->builder->get()->getResult();

		$entities = [];
		for($c = 0; $c < count($results); $c++)
		{
			$dataFile = $this->toEntity($results[$c]);
			$dataFile->user = $userAdapter->toEntity($results[$c]);
			array_push($entities, $dataFile);
		}

		return $entities;
	}

	public function ReadExtensionById(int $id): ?string
	{
		$this->builder->select('disk_name');
		$this->builder->where($this->key, $id);
		$results = $this->builder->get()->getResult();

		$extension = null;

		if (count($results) == 1)
		{
			$diskName = $results[0]->disk_name;
			if (str_contains($diskName, '.'))
			{
				$diskNameArray = explode('.', $diskName);
				$extension = $diskNameArray[count($diskNameArray) - 1];
			}
		}

		return $extension;
	}

	public function UpdateStatus(int $id, int $status): bool
	{
		$this->builder->where($this->key, $id);
		return $this->builder->update(['status' => $status]);
	}

	public function UpdateRecordCount(int $id, int $record_count): bool
	{
		$this->builder->where($this->GetKey(), $id);
		return $this->builder->update(['record_count' => $record_count]);
	}

	public function ReadSourceId(int $id): ?int
	{
		$this->builder->select('source_id');
		$this->builder->where($this->key, $id);
		$results = $this->builder->get()->getResult();

		if (count($results) == 1)
		{
			return $results[0]->source_id;
		}

		return null;
	}
}
