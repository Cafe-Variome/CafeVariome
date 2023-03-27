<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\EAVAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\EAVAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class EAVAdapterFactoryTest extends TestCase
{

    public function testGetInstance()
    {
		$EAVAdapter = (new EAVAdapterFactory())->GetInstance();
		$this->assertIsObject($EAVAdapter);
		$this->assertInstanceOf(EAVAdapter::class, $EAVAdapter);
    }
}
