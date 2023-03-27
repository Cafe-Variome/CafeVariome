<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\Entity;
use App\Libraries\CafeVariome\Entities\NullEntity;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Factory\EntityFactory
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 */
class EntityFactoryTest extends TestCase
{
    public function testGetInstance()
    {
		$object = new \stdClass();
		$entity = (new EntityFactory())->GetInstance($object);
		$this->assertIsObject($entity);
		$this->assertInstanceOf(NullEntity::class, $entity);

		$object->id = 1;
		$entity = (new EntityFactory())->GetInstance($object);
		$this->assertIsObject($entity);
		$this->assertInstanceOf(Entity::class, $entity);
	}
}
