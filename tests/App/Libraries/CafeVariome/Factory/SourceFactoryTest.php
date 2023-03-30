<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\Source;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Entities\Source
 * @covers \App\Libraries\CafeVariome\Factory\SourceFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 */
class SourceFactoryTest extends TestCase
{
	public function testGetInstanceFromParameters()
	{
		$source = (new SourceFactory())->GetInstanceFromParameters(
			uniqid(), uniqid(), uniqid(), uniqid(), uniqid(), uniqid(), uniqid(), rand(1, PHP_INT_MAX), rand(0, PHP_INT_MAX), rand(0, 1), rand(0, 1)
		);
		$this->assertIsObject($source);
		$this->assertInstanceOf(Source::class, $source);
	}
    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->name = uniqid();
		$object->uid = uniqid();
		$object->display_name = uniqid();
		$object->description = uniqid();
		$object->owner_name = uniqid();
		$object->owner_email = uniqid();
		$object->uri = uniqid();
		$object->date_created = rand(1, PHP_INT_MAX);
		$object->record_count = rand(0, PHP_INT_MAX);
		$object->locked = rand(0, 1);
		$object->status = rand(0, 1);

		$source = (new SourceFactory())->GetInstance($object);
		$this->assertIsObject($source);
		$this->assertInstanceOf(Source::class, $source);

		$emptyObject = new \stdClass();
		$nullEntity = (new SourceFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
    }
}
