<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\SourceAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\SourceAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class SourceAdapterFactoryTest extends TestCase
{
    public function testGetInstance()
    {
		$sourceAdapter = (new SourceAdapterFactory())->GetInstance();
		$this->assertIsObject($sourceAdapter);
		$this->assertInstanceOf(SourceAdapter::class, $sourceAdapter);
    }
}
