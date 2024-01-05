<?php namespace App\Libraries\CafeVariome\Database;

/**
 * @author Sadegh Abadijou
 */

use App\Database\Seeds\SubjectsSeeder;
use App\Libraries\CafeVariome\Entities\Subject;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * @covers \App\Libraries\CafeVariome\Database\SubjectAdapterTest::testCountBySourceId
 * @covers \App\Libraries\CafeVariome\Database\SubjectAdapterTest::testReadAllBySourceId
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Database\DataFabricator\SubjectsFabricator
 * @covers \App\Libraries\CafeVariome\Database\SubjectAdapter
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Entities\Subject
 * @covers \App\Libraries\CafeVariome\Factory\SubjectFactory
 */

class SubjectAdapterTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $migrate     = true;
	protected $migrateOnce = false;
	protected $refresh     = true;

	protected $namespace   = 'App\Libraries\CafeVariome\Database';
	protected $seedOnce    = false;
	protected $seed        = 'SubjectsSeeder';

	protected $basePath    = 'app/Database';

	protected function setUp(): void
	{
		parent::setUp();
		$this->config = new Database();
		$seeder = new SubjectsSeeder($this->config);
		$this->insertedData = $seeder->run();
	}

	protected function tearDown(): void
	{
		parent::tearDown();
	}

	public function testCountBySourceId()
	{
		$subjectAdapter = new SubjectAdapter();

		$sourceId = $this->insertedData['insertedData']['source_id'];

		$result = $subjectAdapter->CountBySourceId($sourceId);

		$this->assertIsInt($result);

	}

	public function testReadIdByNameAndSourceId()
	{
		$subjectAdapter = new SubjectAdapter();

		$name = $this->insertedData['insertedData']['name'];
		$sourceId = $this->insertedData['insertedData']['source_id'];

		$result = $subjectAdapter->ReadIdByNameAndSourceId($name, $sourceId);
		$this->assertIsInt($result);

	}

	public function testReadAllBySourceId()
	{
		$subjectAdapter = new SubjectAdapter();

		$sourceId = $this->insertedData['insertedData']['source_id'];;

		$result = $subjectAdapter->ReadAllBySourceId($sourceId);

		$this->assertIsArray($result);

		foreach ($result as $item) {
			$this->assertInstanceOf(Subject::class, $item);
		}
	}
}
