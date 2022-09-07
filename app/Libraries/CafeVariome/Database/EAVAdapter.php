<?php namespace App\Libraries\CafeVariome\Database;

/**
 * EAVAdapter.php
 * Created 18/08/2022
 *
 * This class offers CRUD operation for EAV.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;

class EAVAdapter extends BaseAdapter
{

	public static string $table = 'eavs';

	public static string $key = 'id';

	public function CountBySourceIdAndAttributeIds(int $source_id, array $attribute_ids, bool $unindexedOnly = true): int
	{
		$this->builder->select('id');
		$this->builder->where('source_id', $source_id);
		$this->builder->whereIn('attribute_id', $attribute_ids);
		if($unindexedOnly)
		{
			$this->builder->where('indexed', false);
		}

		return $this->builder->countAllResults();
	}

	public function CountUniqueGroupsBySourceIdAndAttributeIds(int $source_id, array $attribute_ids, bool $unindexedOnly = true): int
	{
		$this->builder->select('group_id, subject_id');
		$this->builder->where('source_id', $source_id);
		$this->builder->whereIn('attribute_id', $attribute_ids);

		if($unindexedOnly)
		{
			$this->builder->where('indexed', false);
		}

		$this->builder->distinct();

		return $this->builder->countAllResults();
	}

	public function CountUniqueSubjectIdsBySourceIdAndAttributeIds(int $source_id, array $attribute_ids, bool $unindexedOnly = true): int
	{
		$this->builder->select('subject_id');
		$this->builder->where('source_id', $source_id);
		$this->builder->whereIn('attribute_id', $attribute_ids);

		if($unindexedOnly)
		{
			$this->builder->where('indexed', false);
		}
		$this->builder->distinct();

		return $this->builder->countAllResults();
	}

	public function ReadUniqueGroupIdsAndSubjectIdsBySourceIdAndAttributeIds(int $source_id, array $attribute_ids, int $limit, int $offset, bool $unindexedOnly = true)
	{
		$this->builder->select('group_id, subject_id, data_file_id');
		$this->builder->distinct();
		$this->builder->where('source_id', $source_id);
		$this->builder->whereIn('attribute_id', $attribute_ids);
		if($unindexedOnly)
		{
			$this->builder->where('indexed', false);
		}
		$this->builder->where('id>', $offset);
		$this->builder->limit($limit);

		return $this->builder->get()->getResult();
	}

	public function ReadLastIdByGroupIdAndSubjectId(int $subject_id, int $group_id): ?int
	{
		$this->builder->select('id');
		$this->builder->where('group_id', $group_id);
		$this->builder->where('subject_id', $subject_id);
		$this->builder->orderBy('id', 'DESC');
		$this->builder->limit(1);

		$query = $this->builder->get()->getResult();
		if (count($query) == 1)
		{
			return $query[0]->id;
		}

		return null;
	}

	public function ReadLastIdBySubjectId(int $subject_id): ?int
	{
		$this->builder->select('id');
		$this->builder->where('subject_id', $subject_id);
		$this->builder->orderBy('id', 'DESC');
		$this->builder->limit(1);

		$query = $this->builder->get()->getResult();
		if (count($query) == 1)
		{
			return $query[0]->id;
		}

		return -1;
	}

	public function ReadBySourceIdAndAttributeIds(int $source_id, array $attribute_ids, int $limit, int $offset, bool $unindexedOnly = true)
	{
		$this->builder->select('id, subject_id, group_id, data_file_id, attribute_id, value_id');
		$this->builder->whereIn('attribute_id', $attribute_ids);
		$this->builder->where('source_id', $source_id);
		if($unindexedOnly)
		{
			$this->builder->where('indexed', false);
		}
		$this->builder->where('id>', $offset);
		$this->builder->limit($limit);

		return $this->builder->get()->getResult();
	}

	public function ReadValueFrequenciesBySourceIdAndFileId(int $source_id, int $file_id)
	{
		$this->builder->select('value_id, count(value_id) as frequency');
		$this->builder->where('source_id', $source_id);
		$this->builder->where('data_file_id', $file_id);
		$this->builder->groupBy('value_id');

		return $this->builder->get()->getResult();
	}

	public function UpdateIndexedBySourceIdAndAttributeIds(int $source_id, array $attribute_ids): bool
	{
		$data = ['indexed' => 1];
		$this->builder->where('source_id', $source_id);
		$this->builder->whereIn('attribute_id', $attribute_ids);

		return $this->builder->update($data);
	}

    /**
     * @inheritDoc
     */
    public function toEntity(?object $object): IEntity
    {
        // TODO: Implement toEntity() method.
    }
}
