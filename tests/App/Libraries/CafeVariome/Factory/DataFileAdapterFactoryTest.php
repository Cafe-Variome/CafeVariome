<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\DataFileAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\DataFileAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class DataFileAdapterFactoryTest extends TestCase
{
    public function testGetInstance()
    {
		$dataFileAdapter = (new DataFileAdapterFactory())->GetInstance();
		$this->assertIsObject($dataFileAdapter);
		$this->assertInstanceOf(DataFileAdapter::class, $dataFileAdapter);
    }
}
