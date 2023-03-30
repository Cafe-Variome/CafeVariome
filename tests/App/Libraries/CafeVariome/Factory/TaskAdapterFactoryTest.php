<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\TaskAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\TaskAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class TaskAdapterFactoryTest extends TestCase
{

    public function testGetInstance()
    {
		$taskAdapter = (new TaskAdapterFactory())->GetInstance();
		$this->assertIsObject($taskAdapter);
		$this->assertInstanceOf(TaskAdapter::class, $taskAdapter);
    }
}
