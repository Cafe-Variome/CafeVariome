<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\ValueMapping;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Entities\ValueMapping
 * @covers \App\Libraries\CafeVariome\Factory\ValueMappingFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 */
class ValueMappingFactoryTest extends TestCase
{
	public function testGetInstanceFromParameters()
	{
		$valueMapping = (new ValueMappingFactory())->GetInstanceFromParameters(uniqid(), rand(1, PHP_INT_MAX));
		$this->assertIsObject($valueMapping);
		$this->assertInstanceOf(ValueMapping::class, $valueMapping);
	}

    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->name = uniqid();
		$object->value_id = rand(1, PHP_INT_MAX);

		$valueMapping = (new ValueMappingFactory())->GetInstance($object);
		$this->assertIsObject($valueMapping);
		$this->assertInstanceOf(ValueMapping::class, $valueMapping);

		$emptyObject = new \stdClass();
		$nullEntity = (new ValueMappingFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
    }
}
