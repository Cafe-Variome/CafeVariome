<?php namespace App\Libraries\CafeVariome\Database;

/**
 * ServerAdapter.php
 * Created 22/04/2022
 *
 * This class offers CRUD operation for Server.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\ServerFactory;


class ServerAdapter extends BaseAdapter
{
	/**
	 * @inheritDoc
	 */
	protected string $table = 'servers';

	/**
	 * @inheritDoc
	 */
	protected string $key = 'id';

	/**
	 * Converts general PHP objects to a Server object.
	 * @param object|null $object
	 * @return IEntity
	 * @throws \Exception
	 */
	public function toEntity(?object $object): IEntity
	{
		$serverFactory = new ServerFactory();
		return $serverFactory->GetInstance($object);
	}
}
