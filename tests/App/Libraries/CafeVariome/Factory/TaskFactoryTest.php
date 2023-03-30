<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\Task;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Controllers\Task
 * @covers \App\Libraries\CafeVariome\Factory\TaskFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 */
class TaskFactoryTest extends TestCase
{

    public function testGetInstanceFromParameters()
    {
		$task = (new TaskFactory())->GetInstanceFromParameters(
			rand(1, PHP_INT_MAX), rand(1, PHP_INT_MAX), rand(1, PHP_INT_MAX), rand(1, PHP_INT_MAX), rand(1, PHP_INT_MAX), uniqid(),
			rand(1, PHP_INT_MAX), rand(1, PHP_INT_MAX), rand(1, PHP_INT_MAX), rand(1, PHP_INT_MAX), rand(1, PHP_INT_MAX), rand(1, PHP_INT_MAX),
		);
		$this->assertIsObject($task);
		$this->assertInstanceOf(Task::class, $task);
    }

    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->user_id = rand(1, PHP_INT_MAX);
		$object->type = rand(1, PHP_INT_MAX);
		$object->progress = rand(1, PHP_INT_MAX);
		$object->status = rand(1, PHP_INT_MAX);
		$object->error_code = rand(1, PHP_INT_MAX);
		$object->error_message = uniqid();
		$object->started = rand(1, PHP_INT_MAX);
		$object->ended = rand(1, PHP_INT_MAX);
		$object->data_file_id = rand(1, PHP_INT_MAX);
		$object->pipeline_id = rand(1, PHP_INT_MAX);
		$object->source_id = rand(1, PHP_INT_MAX);
		$object->overwrite = rand(1, PHP_INT_MAX);

		$task = (new TaskFactory())->GetInstance($object);
		$this->assertIsObject($task);
		$this->assertInstanceOf(Task::class, $task);

		$emptyObject = new \stdClass();
		$nullEntity = (new TaskFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
    }
}
