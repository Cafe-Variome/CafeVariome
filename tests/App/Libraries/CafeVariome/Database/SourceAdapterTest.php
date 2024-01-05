<?php

namespace App\Libraries\CafeVariome\Database;


use App\Libraries\CafeVariome\Factory\SourceAdapterFactory;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Database\Seeds\SourcesSeeder;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * @author Sadegh Abadijou
 */

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Database\SourceAdapter
 * @covers \App\Libraries\CafeVariome\Factory\SourceFactory
 * @covers \App\Libraries\CafeVariome\Factory\SourceAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Source
 * @covers \App\Libraries\CafeVariome\Database\DataFabricator\SourcesFabricator
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */

class SourceAdapterTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $migrate     = true;
	protected $migrateOnce = false;
	protected $refresh     = true;

	protected $namespace   = ' App\Libraries\CafeVariome\Database';
	protected $seedOnce    = false;
	protected $seed        = 'SourcesSeeder';

	protected $basePath    = 'app/Database';


	protected function setUp(): void
	{
		parent::setUp();
		$this->config = new Database();
		$seeder = new SourcesSeeder($this->config);
		$this->insertedData = $seeder->run();
	}
	protected function tearDown(): void
	{
		parent::tearDown();
	}

    public function testLock()
    {
		$dummyID   = $this->insertedData["id"];

		$db        = db_connect($this->config->tests);
		$dbAdapter = new SourceAdapterFactory();

		$dbRecords = $dbAdapter->GetInstance()->Lock((int)$dummyID);
		$query = $db->table('sources')->where('id', (int)$dummyID)->get();
		$isLocked = $query->getResult()[0]->locked;

		$this->assertEquals(1, $isLocked);
    }

    public function testReadUID()
    {
		$dummyID   = $this->insertedData["id"];

		$db        = db_connect($this->config->tests);
		$dbAdapter = new SourceAdapterFactory();

		$dbRecords = $dbAdapter->GetInstance()->ReadUID((int)$dummyID);
		$query = $db->table('sources')->where('id', (int)$dummyID)->get();

		$this->assertNotEmpty($dbRecords);
		$this->assertEquals($dbRecords, $query->getResult()[0]->uid);
    }

    public function testReadAllOnline()
    {
		$db        = db_connect($this->config->tests);
		$dbAdapter = new SourceAdapterFactory();

		$dbRecords = $dbAdapter->GetInstance()->ReadAllOnline();
		$query = $db->table('sources')->where('status', SOURCE_STATUS_ONLINE)->get();

		for ($i = 0; $i < count($dbRecords); $i++)
		{
			$this->assertEquals($dbRecords[$i]->uid, $query->getResult()[$i]->uid);;
		}
    }

    public function testUnlock()
    {
		$dummyID   = $this->insertedData["id"];

		$db        = db_connect($this->config->tests);
		$dbAdapter = new SourceAdapterFactory();

		$dbRecords = $dbAdapter->GetInstance()->Unlock((int)$dummyID);
		$query = $db->table('sources')->where('id', (int)$dummyID)->get();
		$isLocked = $query->getResult()[0]->locked;

		$this->assertEquals(0, $isLocked);
    }

    public function testUpdateRecordCount()
    {
		$dummyID   = $this->insertedData["id"];
		$dummyRecordsCount = 195;

		$db        = db_connect($this->config->tests);
		$dbAdapter = new SourceAdapterFactory();

		$dbRecords = $dbAdapter->GetInstance()->UpdateRecordCount($dummyID, $dummyRecordsCount);

		$query = $db->table('sources')->where('id', (int)$dummyID)->get();
		$recordCount = $query->getResult()[0]->record_count;

		$this->assertEquals($dummyRecordsCount, $recordCount);
    }
}
