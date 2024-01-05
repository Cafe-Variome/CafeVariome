<?php namespace App\Libraries\CafeVariome\Database;

/**
 * @author Sadegh Abadijou
 */

use App\Database\Seeds\PageSeeder;
use App\Libraries\CafeVariome\Factory\PageAdapterFactory;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Database\PageAdapter
 * @covers \App\Libraries\CafeVariome\Factory\PageFactory
 * @covers \App\Libraries\CafeVariome\Factory\PageAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Page
 * @covers \App\Libraries\CafeVariome\Database\DataFabricator\PagesFabricator
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */

class PageAdapterTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $migrate     = true;
	protected $migrateOnce = false;
	protected $refresh     = true;

	protected $namespace   = ' App\Libraries\CafeVariome\Database';
	protected $seedOnce    = false;
	protected $seed        = 'PageSeeder';

	protected $basePath    = 'app/Database';


	protected function setUp(): void
	{
		parent::setUp();
		$this->config = new Database();
		$seeder = new PageSeeder($this->config);
		$this->insertedData = $seeder->run();
	}
	protected function tearDown(): void
	{
		parent::tearDown();
	}

    public function testReadActive()
    {
		$dummyID   = $this->insertedData["id"];

		$db        = db_connect($this->config->tests);

		$dbAdapter = (new PageAdapterFactory())->GetInstance();

		$dbRecords = $dbAdapter->ReadActive($dummyID);
		$query = $db->table('pages')->select('active')->where('id', $dummyID)->get();

		$this->assertEquals($dbRecords->active, $query->getResult()[0]->active);
    }

    public function testActivate()
    {
		$dummyID   = $this->insertedData["id"];

		$db        = db_connect($this->config->tests);

		$dbAdapter = (new PageAdapterFactory())->GetInstance();

		$dbRecords = $dbAdapter->Activate((int)$dummyID);
		$query = $db->table('pages')->select('active')->where('id', $dummyID)->get();

		$this->assertEquals(1, $query->getResult()[0]->active);
    }

    public function testDeactivate()
    {
		$dummyID   = $this->insertedData["id"];

		$db        = db_connect($this->config->tests);

		$dbAdapter = (new PageAdapterFactory())->GetInstance();

		$dbRecords = $dbAdapter->Deactivate((int)$dummyID);
		$query = $db->table('pages')->select('active')->where('id', $dummyID)->get();

		$this->assertEquals(0, $query->getResult()[0]->active);
    }
}
