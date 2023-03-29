<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\ServerAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\ServerAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class ServerAdapterFactoryTest extends TestCase
{
    public function testGetInstance()
    {
		$serverAdapter = (new ServerAdapterFactory())->GetInstance();
		$this->assertIsObject($serverAdapter);
		$this->assertInstanceOf(ServerAdapter::class, $serverAdapter);
    }
}
