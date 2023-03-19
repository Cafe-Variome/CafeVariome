<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\AttributeMappingAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\AttributeMappingAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class AttributeMappingAdapterFactoryTest extends TestCase
{
    public function testGetInstance()
    {
		$attributeMappingAdapter = (new AttributeMappingAdapterFactory())->GetInstance();
		$this->assertIsObject($attributeMappingAdapter);
		$this->assertInstanceOf(AttributeMappingAdapter::class, $attributeMappingAdapter);
    }
}
