<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\NetworkRequest;
use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Factory\NetworkRequestFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Entities\NetworkRequest
 * @covers \App\Libraries\CafeVariome\Factory\NetworkRequestFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 */
class NetworkRequestFactoryTest extends TestCase
{

    public function testGetInstanceFromParameters()
    {
		$networkRequest = (new NetworkRequestFactory())->GetInstanceFromParameters(
			rand(0, 100000), uniqid(), uniqid(), uniqid(), uniqid(), uniqid(), uniqid(), rand(0, 100000)
		);
		$this->assertIsObject($networkRequest);
		$this->assertInstanceOf(NetworkRequest::class, $networkRequest);
    }

    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->network_key = rand(0, 100000);
		$object->installation_key = uniqid();
		$object->url = uniqid();
		$object->justification = uniqid();
		$object->email = uniqid();
		$object->ip = uniqid();
		$object->token = uniqid();
		$object->status = rand(0, 100000);

		$networkRequest = (new NetworkRequestFactory())->GetInstance($object);
		$this->assertIsObject($networkRequest);
		$this->assertInstanceOf(NetworkRequest::class, $networkRequest);

		$emptyObject = new \stdClass();
		$nullEntity = (new NetworkRequestFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
	}
}
