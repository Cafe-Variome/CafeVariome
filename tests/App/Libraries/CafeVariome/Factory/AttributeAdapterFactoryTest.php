<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\AttributeAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\AttributeAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class AttributeAdapterFactoryTest extends TestCase
{
    public function testGetInstance()
    {
		$attributeAdapter = (new AttributeAdapterFactory())->GetInstance();
		$this->assertIsObject($attributeAdapter);
		$this->assertInstanceOf(AttributeAdapter::class, $attributeAdapter);
    }
}
