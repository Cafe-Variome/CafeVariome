<?php namespace App\Libraries\CafeVariome\Database;

/**
 * ValueAdapter.php
 * Created 28/07/2022
 *
 * This class offers CRUD operation for Value.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\ValueFactory;

class ValueAdapter extends BaseAdapter
{

	/**
	 * @inheritDoc
	 */
	protected static string $table = 'values';

	/**
	 * @inheritDoc
	 */
	protected static string $key = 'id';

	public function CountByAttributeId(int $attribute_id): int
	{
		$this->builder->select(static::$key . ', name, display_name, frequency');
		$this->builder->where('attribute_id', $attribute_id);

		return $this->builder->countAll();
	}

    /**
     * @inheritDoc
     */
    public function toEntity(?object $object): IEntity
    {
        $valueFactory = new ValueFactory();
		return $valueFactory->GetInstance($object);
    }
}
