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
	 * @var string name of the corresponding table in the database
	 */
	protected string $table = 'servers';

	/**
	 * @var string primary key of the corresponding table in the database
	 */
	protected string $key = 'id';

	/**
	 * Converts general PHP objects to a Server object.
	 * @param object|null $object
	 * @return IEntity
	 */
	public function toEntity(?object $object): IEntity
	{
		$serverFactory = new ServerFactory();
		return $serverFactory->getInstance($object);
	}
}
