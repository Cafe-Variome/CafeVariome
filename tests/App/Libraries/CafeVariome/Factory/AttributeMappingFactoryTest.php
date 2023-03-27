<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\AttributeMapping;
use App\Libraries\CafeVariome\Entities\NullEntity;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Factory\AttributeMappingFactory
 * @covers \App\Libraries\CafeVariome\Entities\AttributeMapping
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 */
class AttributeMappingFactoryTest extends TestCase
{
    public function testGetInstanceFromParameters()
    {
		$attributeMapping = (new AttributeMappingFactory())->GetInstanceFromParameters('test mapping', 2);
		$this->assertIsObject($attributeMapping);
		$this->assertInstanceOf(AttributeMapping::class, $attributeMapping);
    }

    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->name = 'test mapping';
		$object->attribute_id = 3;
		$attributeMapping = (new AttributeMappingFactory())->GetInstance($object);
		$this->assertIsObject($attributeMapping);
		$this->assertInstanceOf(AttributeMapping::class, $attributeMapping);

		$emptyObject = new \stdClass();
		$nullEntity = (new AttributeMappingFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
    }
}
