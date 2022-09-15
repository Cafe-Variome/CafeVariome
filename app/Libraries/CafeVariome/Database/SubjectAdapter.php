<?php namespace App\Libraries\CafeVariome\Database;

/**
 * SubjectAdapter.php
 * Created 27/07/2022
 *
 * This class offers CRUD operation for Subject.
 * @author Mehdi Mehtarizadeh
 */


use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\SubjectFactory;

class SubjectAdapter extends BaseAdapter
{
	/**
	 * @inheritDoc
	 */
	protected static string $table = 'subjects';

	/**
	 * @inheritDoc
	 */
	protected static string $key = 'id';

	public function CountBySourceId(int $source_id): int
	{
		$this->builder->select(static::$key);
		$this->builder->where('source_id', $source_id);

		return $this->builder->countAllResults();
	}

	public function ReadIdByNameAndSourceId(string $name, int $source_id): ?int
	{
		$this->builder->select(static::$key);
		$this->builder->where('name', $name);
		$this->builder->where('source_id', $source_id);

		$results = $this->builder->get()->getResult();

		if (count($results) == 1)
		{
			return $results[0]->{static::$key};
		}

		return null;
	}

	public function ReadAllBySourceId(int $source_id): array
	{
		$this->CompileSelect();
		$this->CompileJoin();
		$this->builder->where(static::$table . '.source_id', $source_id);

		$results = $this->builder->get()->getResult();

		$entities = [];
		for($c = 0; $c < count($results); $c++)
		{
			$entities[$results[$c]->{static::$key}] = $this->binding != null ? $this->BindTo($results[$c]) : $this->toEntity($results[$c]);
		}

		return $entities;
	}

	/**
	 * Converts general PHP objects to a Subject object.
	 * @param object|null $object
	 * @return IEntity
	 * @throws \Exception
	 */
    public function toEntity(?object $object): IEntity
    {
        $subjectFactory = new SubjectFactory();
		return $subjectFactory->GetInstance($object);
    }
}
