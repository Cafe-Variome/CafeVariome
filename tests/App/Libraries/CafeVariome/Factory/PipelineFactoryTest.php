<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\Pipeline;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Factory\PipelineFactory
 * @covers \App\Libraries\CafeVariome\Entities\Pipeline
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 */
class PipelineFactoryTest extends TestCase
{
    public function testGetInstanceFromParameters()
    {
		$pipeline = (new PipelineFactory())->GetInstanceFromParameters(
			uniqid(), rand(1, PHP_INT_MAX), uniqid(), uniqid(), rand(1, PHP_INT_MAX), rand(1, PHP_INT_MAX), uniqid(), uniqid(), rand(1, PHP_INT_MAX), uniqid(), uniqid()
		);
		$this->assertIsObject($pipeline);
		$this->assertInstanceOf(Pipeline::class, $pipeline);
    }

    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->name = uniqid();
		$object->subject_id_location = rand(1, PHP_INT_MAX);
		$object->subject_id_attribute_name = uniqid();
		$object->subject_id_prefix = uniqid();
		$object->subject_id_assignment_batch_size = rand(1, PHP_INT_MAX);
		$object->expansion_policy = rand(1, PHP_INT_MAX);
		$object->expansion_columns = uniqid();
		$object->expansion_attribute_name = uniqid();
		$object->grouping = rand(1, PHP_INT_MAX);
		$object->group_columns = rand(1, PHP_INT_MAX);
		$object->internal_delimiter = uniqid();
		$pipeline = (new PipelineFactory())->GetInstance($object);
		$this->assertIsObject($pipeline);
		$this->assertInstanceOf(Pipeline::class, $pipeline);

		$emptyObject = new \stdClass();
		$nullEntity = (new PipelineFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
    }
}
