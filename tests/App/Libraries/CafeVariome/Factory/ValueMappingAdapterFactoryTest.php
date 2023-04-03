<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use APP\Libraries\CafeVariome\Database\ValueMappingAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\ValueMappingAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class ValueMappingAdapterFactoryTest extends TestCase
{

    public function testGetInstance()
    {
		$valueMappingAdapter = (new ValueMappingAdapterFactory())->GetInstance();
		$this->assertIsObject($valueMappingAdapter);
		$this->assertInstanceOf(ValueMappingAdapter::class, $valueMappingAdapter);
    }
}
