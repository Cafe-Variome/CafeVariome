<?php namespace APP\Libraries\CafeVariome\Database;

/**
 * NetworkRequestAdapter.php
 * Created 30/01/2023
 *
 * This class offers CRUD operation for NetworkRequest.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\NetworkRequestFactory;

class NetworkRequestAdapter extends BaseAdapter
{
	/**
	 * @inheritDoc
	 */
	protected static string $table = 'network_requests';

	/**
	 * @inheritDoc
	 */
	protected static string $key = 'id';

    /**
     * @inheritDoc
     */
    public function toEntity(?object $object): IEntity
    {
        $networkRequestFactory = new NetworkRequestFactory();
		return $networkRequestFactory->GetInstance($object);
    }
}
