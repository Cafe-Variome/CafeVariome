<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\Network;
use App\Libraries\CafeVariome\Entities\NullEntity;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Entities\Network
 * @covers \App\Libraries\CafeVariome\Factory\NetworkFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 */
class NetworkFactoryTest extends TestCase
{
	public function testGetInstanceFromParameters()
	{
		$network = (new NetworkFactory())->GetInstanceFromParameters(rand(1, 1000000), uniqid());
		$this->assertIsObject($network);
		$this->assertInstanceOf(Network::class, $network);
	}

    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->name = uniqid();
		$network = (new NetworkFactory())->GetInstance($object);

		$this->assertIsObject($network);
		$this->assertInstanceOf(Network::class, $network);

		$emptyObject = new \stdClass();
		$nullEntity = (new NetworkFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
    }
}
