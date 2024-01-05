<?php namespace App\Libraries\CafeVariome\Database;


use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\TaskAdapterFactory;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Database\Seeds\TasksSeeder;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * @author Sadegh Abadijou
 */

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Database\TaskAdapter
 * @covers \App\Libraries\CafeVariome\Factory\TaskFactory
 * @covers \App\Libraries\CafeVariome\Factory\TaskAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Task
 * @covers \App\Libraries\CafeVariome\Database\DataFabricator\TasksFabricator
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */

class TaskAdapterTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $migrate     = true;
	protected $migrateOnce = false;
	protected $refresh     = true;

	protected $namespace   = ' App\Libraries\CafeVariome\Database';
	protected $seedOnce    = false;
	protected $seed        = 'TasksSeeder';

	protected $basePath    = 'app/Database';


	protected function setUp(): void
	{
		parent::setUp();
		$this->config = new Database();
		$seeder = new TasksSeeder($this->config);
		$this->insertedData = $seeder->run();
	}
	protected function tearDown(): void
	{
		parent::tearDown();
	}
    public function testReadLastProcessingTaskIdBySourceIdAndType()
    {
		$dummyTaskType = $this->insertedData['insertedData']['type'];
		$dummySourceID = $this->insertedData['insertedData']['source_id'];
		$_GLOBALS[TASK_STATUS_PROCESSING] = $this->insertedData['insertedData']['status'];

		$dbAdapter = (new TaskAdapterFactory())->GetInstance();
		$db        = db_connect($this->config->tests);

		$id = $this->db->table('tasks')
			->where('type', $dummyTaskType)
			->where('source_id', $dummySourceID)
			->where('status', TASK_STATUS_PROCESSING)->get()->getResult();

		$selectedId = $dbAdapter->ReadLastProcessingTaskIdBySourceIdAndType($dummySourceID, $dummyTaskType);

		$this->assertNotNull($selectedId);
	}
    public function testReadByDataFileId()
    {
		$dummyDataFileID = $this->insertedData['insertedData']['data_file_id'];

		$dbAdapter = (new TaskAdapterFactory())->GetInstance();
		$db        = db_connect($this->config->tests);

		$dbRecords = $dbAdapter->ReadByDataFileId($dummyDataFileID);

		$records = $db->table('tasks')->where('data_file_id', $dummyDataFileID)->get()->getResult();

		$counter = 0;
		foreach($dbRecords as $record)
		{
			$this->assertEquals($records[$counter]->data_file_id, $record->data_file_id);
			$counter = $counter + 1;
		}

    }
    public function testToEntity()
    {
		$object = (object) $this->insertedData['insertedData'];

		$dbAdapter = (new TaskAdapterFactory())->GetInstance();

		$instance = $dbAdapter->toEntity($object);

		$this->assertInstanceOf(IEntity::class, $instance);
    }
}
