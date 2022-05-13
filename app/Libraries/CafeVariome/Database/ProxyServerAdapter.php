<?php namespace App\Libraries\CafeVariome\Database;

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\ProxyServerFactory;

class ProxyServerAdapter extends BaseAdapter
{
	/**
	 * @inheritDoc
	 */
	protected string $table = 'proxy_servers';

	/**
	 * @inheritDoc
	 */
	protected string $key = 'id';

	/**
	 * @inheritDoc
	 * Please do not change the order of foreign keys in the array.
	 */
	protected array $foreign_keys = ['server_id', 'credential_id'];

	/**
     * @inheritDoc
     */
    public function toEntity(?object $object): IEntity
    {
        $proxyServerFactory = new ProxyServerFactory();
		return $proxyServerFactory->getInstance($object);
    }
}
