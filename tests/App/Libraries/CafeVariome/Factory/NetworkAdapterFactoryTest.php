<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\NetworkAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\NetworkAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class NetworkAdapterFactoryTest extends TestCase
{
    public function testGetInstance()
    {
		$networkAdapter = (new NetworkAdapterFactory())->GetInstance();
		$this->assertIsObject($networkAdapter);
		$this->assertInstanceOf(NetworkAdapter::class, $networkAdapter);
    }
}
