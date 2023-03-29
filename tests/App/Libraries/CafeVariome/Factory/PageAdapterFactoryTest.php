<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\PageAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\PageAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class PageAdapterFactoryTest extends TestCase
{
    public function testGetInstance()
    {
		$pageAdapter = (new PageAdapterFactory())->GetInstance();
		$this->assertIsObject($pageAdapter);
		$this->assertInstanceOf(PageAdapter::class, $pageAdapter);
    }
}
