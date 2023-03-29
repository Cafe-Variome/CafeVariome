<?php namespace App\Libraries\CafeVariome\Entities;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Factory\ProxyServerFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Entities\ProxyServer
 * @covers \App\Libraries\CafeVariome\Factory\ProxyServerFactory
 */
class ProxyServerTest extends TestCase
{
	public function test__construct()
	{
		$proxyServer = (new ProxyServerFactory())->GetInstanceFromParameters(
			'test', 80, 1, 2
		);

		$this->assertSame('test', $proxyServer->name);
		$this->assertEquals(80, $proxyServer->port);
		$this->assertEquals(1, $proxyServer->server_id);
		$this->assertEquals(2, $proxyServer->credential_id);
	}

	public function testToArray()
	{
		$proxyServer = (new ProxyServerFactory())->GetInstanceFromParameters(
			'test', 80, 1, 1
		);

		$this->assertIsArray($proxyServer->toArray());
	}
}
