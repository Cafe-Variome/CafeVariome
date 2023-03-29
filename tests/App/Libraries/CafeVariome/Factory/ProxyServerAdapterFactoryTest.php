<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\ProxyServerAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\ProxyServerAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class ProxyServerAdapterFactoryTest extends TestCase
{
    public function testGetInstance()
    {
		$proxyServerAdapter = (new ProxyServerAdapterFactory())->GetInstance();
		$this->assertIsObject($proxyServerAdapter);
		$this->assertInstanceOf(ProxyServerAdapter::class, $proxyServerAdapter);
    }
}
