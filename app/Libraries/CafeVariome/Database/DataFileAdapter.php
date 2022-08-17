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
	protected static string $table = 'data_files';

	/**
	 * @inheritDoc
	 */
	protected static string $key = 'id';

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
		$this->CompileSelect();
		$this->CompileJoin();
		$this->builder->where('source_id', $source_id);
		$results = $this->builder->get()->getResult();

		$entities = [];
		for($c = 0; $c < count($results); $c++)
		{
			$entities[$results[$c]->{static::$key}] = $this->binding != null ? $this->BindTo($results[$c]) : $this->toEntity($results[$c]);
		}

		return $entities;
	}

	public function ReadExtensionById(int $id): ?string
	{
		$this->builder->select('disk_name');
		$this->builder->where(static::$key, $id);
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
		$this->builder->where(static::$key, $id);
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
		$this->builder->where(static::$key, $id);
		$results = $this->builder->get()->getResult();

		if (count($results) == 1)
		{
			return $results[0]->source_id;
		}

		return null;
	}
}
