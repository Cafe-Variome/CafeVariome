<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\DiscoveryGroupAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\DiscoveryGroupAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class DiscoveryGroupAdapterFactoryTest extends TestCase
{

    public function testGetInstance()
    {
		$discoveryGroupAdapter = (new DiscoveryGroupAdapterFactory())->GetInstance();
		$this->assertIsObject($discoveryGroupAdapter);
		$this->assertInstanceOf(DiscoveryGroupAdapter::class, $discoveryGroupAdapter);
    }
}
