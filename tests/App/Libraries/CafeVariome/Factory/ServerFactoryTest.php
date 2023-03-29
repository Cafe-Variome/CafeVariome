<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\Server;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Factory\ServerFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Entities\Server
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 */
class ServerFactoryTest extends TestCase
{
    public function testGetInstanceFromParameters()
    {
		$server = (new ServerFactory())->GetInstanceFromParameters(
			'test', 80, 1
		);

		$this->assertIsObject($server);
		$this->assertInstanceOf(Server::class, $server);
    }

    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->name = 'test';
		$object->address = 'http://localhost.local/';
		$object->removable = true;
		$server = (new ServerFactory())->GetInstance($object);

		$this->assertIsObject($server);
		$this->assertInstanceOf(Server::class, $server);

		$emptyObject = new \stdClass();
		$nullEntity = (new ServerFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
    }
}
