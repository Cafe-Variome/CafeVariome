<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\DiscoveryGroup;
use App\Libraries\CafeVariome\Entities\NullEntity;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Factory\DiscoveryGroupFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Entities\DiscoveryGroup
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 */
class DiscoveryGroupFactoryTest extends TestCase
{
	public function testGetInstanceFromParameters()
	{
		$discoveryGroup = (new DiscoveryGroupFactory())->GetInstanceFromParameters('test name', 'test description', 1, DISCOVERY_GROUP_POLICY_BOOLEAN);
		$this->assertIsObject($discoveryGroup);
		$this->assertInstanceOf(DiscoveryGroup::class, $discoveryGroup);

    }
    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->name = 'test name';
		$object->description = 'test description';
		$object->network_id = 1;
		$object->policy = DISCOVERY_GROUP_POLICY_BOOLEAN;

		$discoveryGroup = (new DiscoveryGroupFactory())->GetInstance($object);
		$this->assertIsObject($discoveryGroup);
		$this->assertInstanceOf(DiscoveryGroup::class, $discoveryGroup);

		$emptyObject = new \stdClass();
		$nullEntity = (new DiscoveryGroupFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
	}
}
