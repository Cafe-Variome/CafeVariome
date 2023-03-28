<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use APP\Libraries\CafeVariome\Database\NetworkRequestAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\NetworkRequestAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class NetworkRequestAdapterFactoryTest extends TestCase
{
    public function testGetInstance()
    {
		$networkRequestAdapter = (new NetworkRequestAdapterFactory())->GetInstance();
		$this->assertIsObject($networkRequestAdapter);
		$this->assertInstanceOf(NetworkRequestAdapter::class, $networkRequestAdapter);
    }
}
