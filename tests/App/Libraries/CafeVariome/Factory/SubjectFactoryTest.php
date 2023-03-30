<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\Subject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Entities\Subject
 * @covers \App\Libraries\CafeVariome\Factory\SubjectFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 */
class SubjectFactoryTest extends TestCase
{
    public function testGetInstanceFromParameters()
    {
		$subject = (new SubjectFactory())->GetInstanceFromParameters(uniqid(), rand(1, PHP_INT_MAX), uniqid());
		$this->assertIsObject($subject);
		$this->assertInstanceOf(Subject::class, $subject);
    }

    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->name = uniqid();
		$object->source_id = rand(1, PHP_INT_MAX);
		$object->display_name = uniqid();
		$subject = (new SubjectFactory())->GetInstance($object);

		$this->assertIsObject($subject);
		$this->assertInstanceOf(Subject::class, $subject);

		$emptyObject = new \stdClass();
		$nullEntity = (new SubjectFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
    }
}
