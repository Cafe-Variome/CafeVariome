<?php namespace App\Libraries\CafeVariome\Database;

/**
 * ProxyServerAdapter.php
 * Created 12/05/2022
 *
 * This class offers CRUD operation for ProxyServer.
 * @author Mehdi Mehtarizadeh
 */


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
		return $proxyServerFactory->GetInstance($object);
    }
}
