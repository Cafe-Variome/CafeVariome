<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\Attribute;
use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Factory\AttributeFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Factory\AttributeFactory
 * @covers \App\Libraries\CafeVariome\Entities\Attribute
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 */
class AttributeFactoryTest extends TestCase
{
    public function testGetInstanceFromParameters()
    {
		$attribute = (new AttributeFactory())->GetInstanceFromParameters(
			'test attrib', 9, 'test attrib display'
		);

		$this->assertIsObject($attribute);
		$this->assertInstanceOf(Attribute::class, $attribute);
    }

    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->name = 'test attrib';
		$object->source_id = 9;
		$object->display_name = 'test attrib display';
		$object->type = ATTRIBUTE_TYPE_UNDEFINED;
		$object->min = 0.0;
		$object->max = 0.0;
		$object->show_in_interface = false;
		$object->include_in_interface_index = true;
		$object->storage_location = ATTRIBUTE_STORAGE_UNDEFINED;
		$attribute = (new AttributeFactory())->GetInstance($object);

		$this->assertIsObject($attribute);
		$this->assertInstanceOf(Attribute::class, $attribute);

		$emptyObject = new \stdClass();
		$nullEntity = (new AttributeFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
    }
}
