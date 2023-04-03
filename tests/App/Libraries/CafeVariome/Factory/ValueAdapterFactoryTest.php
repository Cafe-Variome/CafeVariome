<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\ValueAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\ValueAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class ValueAdapterFactoryTest extends TestCase
{
    public function testGetInstance()
    {
		$valueAdapter = (new ValueAdapterFactory())->GetInstance();
		$this->assertIsObject($valueAdapter);
		$this->assertInstanceOf(ValueAdapter::class, $valueAdapter);
    }
}
