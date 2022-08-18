<?php namespace App\Libraries\CafeVariome\Database;

/**
 * PageAdapter.php
 * Created 17/08/2022
 *
 * This class offers CRUD operation for Page.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\PageFactory;

class PageAdapter extends BaseAdapter
{
	/**
	 * @inheritdoc
	 */
	protected static string $table = 'pages';

	/**
	 * @inheritdoc
	 */
	protected static string $key = 'id';

	public function Activate(int $id): bool
	{
		$this->builder->where(static::$key, $id);
		return $this->builder->update(['active' => 1]);
	}

	public function Deactivate(int $id): bool
	{
		$this->builder->where(static::$key, $id);
		return $this->builder->update(['active' => 0]);
	}

	public function ReadActive(int $id): IEntity
	{
		$this->CompileSelect();
		$this->CompileJoin();

		$this->builder->where(static::$table . '.active', 1);
		$this->builder->where(static::$table . '.' .static::$key, $id);
		$results = $this->builder->get()->getResult();

		$record = null;
		if (count($results) == 1)
		{
			$record = $results[0];
		}

		return $this->binding != null ? $this->BindTo($record) : $this->toEntity($record);
	}


	/**
     * @inheritDoc
     */
    public function toEntity(?object $object): IEntity
    {
        return (new PageFactory())->GetInstance($object);
    }
}
