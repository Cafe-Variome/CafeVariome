<?php namespace App\Libraries\CafeVariome\Database;

/**
 * @author Sadegh Abadijou
 */

use App\Database\Seeds\DataFileSeeder;
use App\Libraries\CafeVariome\Factory\DataFileAdapterFactory;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Database\DataFileAdapter
 * @covers \App\Libraries\CafeVariome\Factory\DataFileFactory
 * @covers \App\Libraries\CafeVariome\Factory\DataFileAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\DataFile
 * @covers \App\Libraries\CafeVariome\Database\DataFabricator\DataFileFabricator
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */

class DataFileAdapterTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $migrate     = true;
	protected $migrateOnce = false;
	protected $refresh     = true;

	protected $namespace   = ' App\Libraries\CafeVariome\Database';
	protected $seedOnce    = false;
	protected $seed        = 'DataFileSeeder';

	protected $basePath    = 'app/Database';

	protected function setUp(): void
	{
		parent::setUp();
		$this->config = new Database();
		$seeder = new DataFileSeeder($this->config);
		$this->insertedData = $seeder->run();
	}

	protected function tearDown(): void
	{
		parent::tearDown();
	}
	public function testReadBySourceId()
	{
		$dummySourceID   = $this->insertedData["insertedData"]["source_id"];

		$db        = db_connect($this->config->tests);
		$dbAdapter = (new DataFileAdapterFactory())->GetInstance();

		$dbRecords = $dbAdapter->ReadBySourceId((int)$dummySourceID);
		$query = $db->table('data_files')->where('source_id', (int)$dummySourceID)->get();
		$counter = 0;
		foreach ($dbRecords as $key => $element) {
			$this->assertEquals($element->disk_name, $query->getResult()[$counter]->disk_name);
			$counter = $counter + 1;
		}
	}

	public function testReadSourceId()
	{
		$dummyID   = $this->insertedData["id"];

		$db        = db_connect($this->config->tests);
		$dbAdapter = (new DataFileAdapterFactory())->GetInstance();

		$dbRecords = $dbAdapter->ReadSourceId((int)$dummyID);
		$query = $db->table('data_files')->where('id', (int)$dummyID)->get();
		$record = $query->getResult();

		$this->assertEquals($dbRecords, $record[0]->source_id);
	}

	public function testUpdateStatus()
	{
		$dummyID   = $this->insertedData["id"];
		$dummyStatus = array_rand([0, 1, 2, 3, 4]);
		$db        = db_connect($this->config->tests);

		$dbAdapter = (new DataFileAdapterFactory())->GetInstance();

		$dbRecords = $dbAdapter->UpdateStatus((int)$dummyID, $dummyStatus);
		$query = $db->table('data_files')->select('status')->where('id', $dummyID)->get();

		$this->assertEquals($dummyStatus, $query->getResult()[0]->status);
	}

	public function testCountUploadedAndImportedBySourceId()
	{
		$dummySourceID   = $this->insertedData["insertedData"]["source_id"];

		$db        = db_connect($this->config->tests);
		$dbAdapter = (new DataFileAdapterFactory())->GetInstance();

		$dbRecordsNum = $dbAdapter->CountUploadedAndImportedBySourceId((int)$dummySourceID);
		$query = $db->table('data_files')->where('source_id', (int)$dummySourceID)->
		whereIn('status', [DATA_FILE_STATUS_UPLOADED, DATA_FILE_STATUS_IMPORTED])->get();
		$records = $query->getResult();

		$this->assertEquals($dbRecordsNum, count($records));
	}

	public function testReadExtensionById()
	{
		$dummyID   = $this->insertedData["id"];

		$db        = db_connect($this->config->tests);
		$dbAdapter = (new DataFileAdapterFactory())->GetInstance();

		$dbRecords = $dbAdapter->ReadExtensionById((int)$dummyID);

		$query = $db->table('data_files')->where('id', (int)$dummyID)->get();
		$diskNameArray = explode('.', $query->getResult()[0]->disk_name);
		$extension = $diskNameArray[count($diskNameArray) - 1];

		$this->assertEquals($dbRecords, $extension);
	}

	public function testUpdateRecordCount()
	{
		$dummyID   = $this->insertedData["id"];
		$dummyNewCount = 195;
		$db        = db_connect($this->config->tests);

		$dbAdapter = (new DataFileAdapterFactory())->GetInstance();

		$dbRecords = $dbAdapter->UpdateRecordCount((int)$dummyID, $dummyNewCount);
		$query = $db->table('data_files')->select('record_count')->where('id', $dummyID)->get();

		$this->assertEquals($dummyNewCount, $query->getResult()[0]->record_count);
	}

	public function testReadUploadedAndImportedIdsBySourceId()
	{
		$dummySourceID   = $this->insertedData["insertedData"]["source_id"];

		$db        = db_connect($this->config->tests);
		$dbAdapter = (new DataFileAdapterFactory())->GetInstance();

		$ids = $dbAdapter->ReadUploadedAndImportedIdsBySourceId((int)$dummySourceID);
		$query = $db->table('data_files')->select('id')->where('source_id', $dummySourceID)
					->whereIn('status', [DATA_FILE_STATUS_UPLOADED, DATA_FILE_STATUS_IMPORTED])->get();

		$counter = 0;
		foreach ($ids as $id) {
			$this->assertEquals((int)$id, $query->getResult()[$counter]->id);
			$counter = $counter + 1;
		}
	}
}
