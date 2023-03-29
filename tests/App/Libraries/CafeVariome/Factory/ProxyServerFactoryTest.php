<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\ProxyServer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Factory\ProxyServerFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Entities\ProxyServer
 */
class ProxyServerFactoryTest extends TestCase
{
    public function testGetInstanceFromParameters()
    {
		$proxyServer = (new ProxyServerFactory())->GetInstanceFromParameters(
			'test', 80, 1, 2
		);

		$this->assertIsObject($proxyServer);
		$this->assertInstanceOf(ProxyServer::class, $proxyServer);
    }

    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->name = 'test';
		$object->port = 8080;
		$object->server_id = 5;
		$object->credential_id = 3;
		$proxyServer = (new ProxyServerFactory())->GetInstance($object);

		$this->assertIsObject($proxyServer);
		$this->assertInstanceOf(ProxyServer::class, $proxyServer);

		$emptyObject = new \stdClass();

		$nullEntity = (new ProxyServerFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
	}
}
