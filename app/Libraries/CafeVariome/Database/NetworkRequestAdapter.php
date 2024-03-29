<?php namespace App\Libraries\CafeVariome\Database;

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

	public function CountAllPending(): int
	{
		$this->builder->select('id');
		$this->builder->where('status', NETWORKREQUEST_PENDING);
		return $this->builder->countAllResults();
	}

    /**
     * @inheritDoc
     */
    public function toEntity(?object $object): IEntity
    {
        $networkRequestFactory = new NetworkRequestFactory();
		return $networkRequestFactory->GetInstance($object);
    }
}
