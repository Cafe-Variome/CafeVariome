<?php namespace APP\Libraries\CafeVariome\Database;

/**
 * ValueMappingAdapter.php
 * Created 19/12/2022
 *
 * This class offers CRUD operation for ValueMapping.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\ValueMappingFactory;

class ValueMappingAdapter extends BaseAdapter
{
	/**
	 * @inheritdoc
	 */
	protected static string $key = 'id';

	/**
	 * @inheritdoc
	 */
	protected static string $table = 'value_mappings';

	public function ReadByValueId(int $value_id): array
	{
		$this->CompileSelect();
		$this->CompileJoin();
		$this->builder->where(static::$table . '.value_id', $value_id);
		$results = $this->builder->get()->getResult();

		$entities = [];
		for($c = 0; $c < count($results); $c++)
		{
			$entities[$results[$c]->{static::$key}] = $this->binding != null ? $this->BindTo($results[$c]) : $this->toEntity($results[$c]);
		}

		return $entities;
	}

    /**
     * @inheritDoc
     */
    public function toEntity(?object $object): IEntity
    {
        $valueMappingFactory = new ValueMappingFactory();
		return $valueMappingFactory->GetInstance($object);
    }
}
