<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\GroupAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\GroupAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class GroupAdapterFactoryTest extends TestCase
{
    public function testGetInstance()
    {
		$groupAdapter = (new GroupAdapterFactory())->GetInstance();
		$this->assertIsObject($groupAdapter);
		$this->assertInstanceOf(GroupAdapter::class, $groupAdapter);
    }
}
