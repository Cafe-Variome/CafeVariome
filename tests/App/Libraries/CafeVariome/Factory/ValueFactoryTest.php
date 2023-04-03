<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\Value;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Entities\Value
 * @covers \App\Libraries\CafeVariome\Factory\ValueFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Factory\EntityFactory
 */
class ValueFactoryTest extends TestCase
{
    public function testGetInstanceFromParameters()
    {
		$value = (new ValueFactory())->GetInstanceFromParameters(
			uniqid(), rand(1, PHP_INT_MAX), uniqid(), rand(1, PHP_INT_MAX), rand(0, 1), rand(0, 1)
		);
		$this->assertIsObject($value);
		$this->assertInstanceOf(Value::class, $value);
    }

    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->name = uniqid();
		$object->attribute_id = rand(1, PHP_INT_MAX);
		$object->display_name = uniqid();
		$object->frequency = rand(1, PHP_INT_MAX);
		$object->show_in_interface = rand(0, 1);
		$object->include_in_interface_index = rand(0, 1);

		$value = (new ValueFactory())->GetInstance($object);
		$this->assertIsObject($value);
		$this->assertInstanceOf(Value::class, $value);

		$emptyObject = new \stdClass();
		$nullEntity = (new ValueFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
    }
}
