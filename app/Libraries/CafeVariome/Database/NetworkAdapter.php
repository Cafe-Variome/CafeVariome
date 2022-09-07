<?php namespace App\Libraries\CafeVariome\Database;

/**
 * NetworkAdapter.php
 * Created 05/09/2022
 *
 * This class offers CRUD operation for Network.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\NetworkFactory;

class NetworkAdapter extends BaseAdapter
{
	/**
	 * @inheritDoc
	 */
	protected static string $table = 'networks';

	/**
	 * @inheritDoc
	 */
	protected static string $key = 'id';

    /**
     * @inheritDoc
     */
    public function toEntity(?object $object): IEntity
    {
        $networkFactory = new NetworkFactory();
		return $networkFactory->GetInstance($object);
    }
}
