<?php namespace App\Libraries\CafeVariome\Database;


/**
 * @author Sadegh Abadijou
 */

use App\Database\Seeds\GroupSeeder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Database\GroupAdapter
 * @covers \App\Libraries\CafeVariome\Factory\GroupAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Group
 * @covers \App\Libraries\CafeVariome\Database\DataFabricator\GroupsFabricator
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */

class GroupAdapterTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $migrate     = true;
	protected $migrateOnce = false;
	protected $refresh     = true;

	protected $namespace   = ' App\Libraries\CafeVariome\Database';
	protected $seedOnce    = false;
	protected $seed        = 'GroupSeeder';

	protected $basePath    = 'app/Database';

	protected function setUp(): void
	{
		parent::setUp();
		$this->config = new Database();
		$seeder = new GroupSeeder($this->config);
		$this->insertedData = $seeder->run();
	}

	protected function tearDown(): void
	{
		parent::tearDown();
	}

    public function testAddAttributes()
    {
		$dbAdapter = new GroupAdapter();
		$groupId = $this->insertedData['id'];
		$attributeIds = [1, 2, 3];

		$dbAdapter->AddAttributes($groupId, $attributeIds);

		$this->assertTrue(true);
	}

    public function testReadAttributeIds()
    {
		$dbAdapter = new GroupAdapter();
		$groupId = $this->insertedData['id'];
		$attributeIds = $dbAdapter->ReadAttributeIds($groupId);

		$this->assertNotNull($attributeIds);
	}

    public function testReadIdByNameAndSourceId()
    {
		$dbAdapter = new GroupAdapter();
		$groupName = $this->insertedData['insertedData']['name'];
		$sourceId = $this->insertedData['insertedData']['source_id'];
		$groupId = $dbAdapter->ReadIdByNameAndSourceId($groupName, $sourceId);

		$this->assertNotNull($groupId);
		$this->assertEquals($this->insertedData['id'], $groupId);
    }

}
