<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\SubjectAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\SubjectAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class SubjectAdapterFactoryTest extends TestCase
{
    public function testGetInstance()
    {
		$subjectAdapter = (new SubjectAdapterFactory())->GetInstance();
		$this->assertIsObject($subjectAdapter);
		$this->assertInstanceOf(SubjectAdapter::class, $subjectAdapter);
    }
}
