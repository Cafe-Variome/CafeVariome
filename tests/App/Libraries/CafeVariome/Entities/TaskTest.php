<?php namespace App\Libraries\CafeVariome\Entities;

use PHPUnit\Framework\TestCase;


/**
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Entities\Task
 * @covers \App\Libraries\CafeVariome\Helpers\UI\TaskHelper
 */

class TaskTest extends TestCase
{
	public function testSetError()
	{
		$properties = [
			'data_file_id' => 1,
			'type' => 2,
			'started' => 1624356000,
			'ended' => 1624362000,
			'user_id' => 3,
			'pipeline_id' => 4,
			'source_id' => 5,
			'progress' => 50,
			'error_code' => 6,
			'overwrite' => true,
			'error_message' => 'No pipeline found',
			'status' => 7,
		];
		$task = new Task($properties);

		$task->SetError(1, 'Additional error message');
		$this->assertEquals(1, $task->error_code);
		$this->assertEquals('Runtime Error Additional error message', $task->error_message);

		$task->SetError(2, 'Additional error message');
		$this->assertEquals(2, $task->error_code);
		$this->assertEquals('No data file ID given Additional error message', $task->error_message);

		$task->SetError(3, 'Additional error message');
		$this->assertEquals(3, $task->error_code);
		$this->assertEquals('No pipeline ID given Additional error message', $task->error_message);

		$task->SetError(4, 'Additional error message');
		$this->assertEquals(4, $task->error_code);
		$this->assertEquals('No source ID given Additional error message', $task->error_message);

		$task->SetError(5, 'Additional error message');
		$this->assertEquals(5, $task->error_code);
		$this->assertEquals('No data file found Additional error message', $task->error_message);

		$task->SetError(6, 'Additional error message');
		$this->assertEquals(6, $task->error_code);
		$this->assertEquals('No pipeline found Additional error message', $task->error_message);

		$task->SetError(7, 'Additional error message');
		$this->assertEquals(7, $task->error_code);
		$this->assertEquals('Duplicate task Additional error message', $task->error_message);

		$task->SetError(8, 'Additional error message');
		$this->assertEquals(8, $task->error_code);
		$this->assertEquals('Data file could not be read. Additional error message', $task->error_message);

		$task->SetError(9, 'Additional error message');
		$this->assertEquals(9, $task->error_code);
		$this->assertEquals('Data file could not be saved. Additional error message', $task->error_message);

		$task->SetError(0, 'Additional error message');
		$this->assertEquals(0, $task->error_code);
		$this->assertNull(null,$task->error_code);

	}
}
