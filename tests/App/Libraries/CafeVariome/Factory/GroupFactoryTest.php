<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\Group;
use App\Libraries\CafeVariome\Entities\NullEntity;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Entities\Group
 * @covers \App\Libraries\CafeVariome\Factory\GroupFactory
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class GroupFactoryTest extends TestCase
{
	public function testGetInstanceFromParameters()
	{
		$group = (new GroupFactory())->GetInstanceFromParameters('test group', 7, 'test display name');
		$this->assertIsObject($group);
		$this->assertInstanceOf(Group::class, $group);
	}

    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->name = 'test group';
		$object->source_id = 7;
		$object->display_name = 'test display name';

		$group = (new GroupFactory())->GetInstance($object);
		$this->assertIsObject($group);
		$this->assertInstanceOf(Group::class, $group);

		$emptyObject = new \stdClass();
		$nullEntity = (new GroupFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
    }
}
