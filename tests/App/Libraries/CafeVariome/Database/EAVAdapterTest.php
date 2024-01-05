<?php namespace App\Libraries\CafeVariome\Database;


/**
 * @author Sadegh Abadijou
 */

use App\Database\Seeds\EAVSeeder;
use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\EAVAdapterFactory;
use App\Libraries\CafeVariome\Database\EAVAdapter;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Database\EAVAdapter
 * @covers \App\Libraries\CafeVariome\Factory\EAVAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\EAV
 * @covers \App\Libraries\CafeVariome\Database\DataFabricator\EAVFabricator
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */

class EAVAdapterTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $migrate     = true;
	protected $migrateOnce = false;
	protected $refresh     = true;

	protected $namespace   = ' App\Libraries\CafeVariome\Database';
	protected $seedOnce    = false;
	protected $seed        = 'EAVSeeder';

	protected $basePath    = 'app/Database';

	protected function setUp(): void
	{
		parent::setUp();
		$this->config = new Database();
		$seeder = new EAVSeeder($this->config);
		$this->insertedData = $seeder->run();
	}

	protected function tearDown(): void
	{
		parent::tearDown();
	}

    public function testDeleteBySourceId()
    {
		$dbAdapter = new EAVAdapter();
		$sourceIdToDelete = $this->insertedData['id'];
		$result = $dbAdapter->DeleteBySourceId($sourceIdToDelete);
		$this->assertTrue($result);
		$this->assertNull($this->db->table('eavs')->getWhere(['source_id' => $sourceIdToDelete])->getRow());
    }
	/***
	public function testToEntity()
	{
		$object = (object) $this->insertedData;

		$dbAdapter = (new EAVAdapterFactory())->GetInstance();

		$instance = $dbAdapter->toEntity($object);

		$this->assertInstanceOf(IEntity::class, $instance);
	}
	*/
    public function testDeleteByFileId()
    {
		$dbAdapter = new EAVAdapter();
		$fileIdToDelete = $this->insertedData['id'];
		$result = $dbAdapter->DeleteByFileId($fileIdToDelete);
		$this->assertTrue($result);
		$this->assertNull($this->db->table('eavs')->getWhere(['data_file_id' => $fileIdToDelete])->getRow());
	}

    public function testReadUniqueSubjectIdsAndFileIdsBySourceIdAndAttributeIds()
	{
		$sourceId = 1;
		$attributeIds = [1, 2, 3];
		$limit = 10;
		$offset = 0;
		$unindexedOnly = true;

		$dbAdapter = new EAVAdapter();

		$result = $dbAdapter->ReadUniqueSubjectIdsAndFileIdsBySourceIdAndAttributeIds($sourceId, $attributeIds, $limit, $offset, $unindexedOnly);

		$this->assertIsArray($result);

		foreach ($result as $row) {
			$this->assertObjectHasAttribute('subject_id', $row);
			$this->assertObjectHasAttribute('data_file_id', $row);
		}
	}

    public function testReadUniqueSubjectIdsBySourceId()
    {
		$sourceId = $this->insertedData['insertedData']['source_id'];

		$dbAdapter = new EAVAdapter();

		$result = $dbAdapter->ReadUniqueSubjectIdsBySourceId($sourceId);

		$this->assertIsArray($result);

		foreach ($result as $subjectId) {
			$this->assertIsNumeric($subjectId);
		}
    }

    public function testCountUniqueGroupsBySourceIdAndAttributeIds()
    {

		$sourceId = $this->insertedData['insertedData']['source_id'];
		$attributeIds = [1, 2, 3];
		$unindexedOnly = true;

		$dbAdapter = new EAVAdapter();
		$result = $dbAdapter->CountUniqueGroupsBySourceIdAndAttributeIds($sourceId, $attributeIds, $unindexedOnly);

		$this->assertIsInt($result);
		$this->assertGreaterThanOrEqual(0, $result);
    }

    public function testReadBySourceIdAndAttributeIds()
    {
		$sourceId = $this->insertedData['insertedData']['source_id'];
		$attributeIds = [1, 2, 3];
		$limit = 10;
		$offset = 0;
		$unindexedOnly = true;

		$dbAdapter = new EAVAdapter();
		$result = $dbAdapter->ReadBySourceIdAndAttributeIds($sourceId, $attributeIds, $limit, $offset, $unindexedOnly);

		$this->assertIsArray($result);

		foreach ($result as $row) {
			$this->assertObjectHasAttribute('id', $row);
			$this->assertObjectHasAttribute('subject_id', $row);
			$this->assertObjectHasAttribute('group_id', $row);
			$this->assertObjectHasAttribute('data_file_id', $row);
			$this->assertObjectHasAttribute('attribute_id', $row);
			$this->assertObjectHasAttribute('value_id', $row);
		}
    }

    public function testCountBySourceIdAndAttributeIds()
    {
		$sourceId = $this->insertedData['insertedData']['source_id'];
		$attributeIds = [1, 2, 3];
		$unindexedOnly = true;

		$dbAdapter = new EAVAdapter();
		$result = $dbAdapter->CountBySourceIdAndAttributeIds($sourceId, $attributeIds, $unindexedOnly);

		$this->assertIsInt($result);
		$this->assertGreaterThanOrEqual(0, $result);
    }

	public function testReadValueFrequenciesBySourceIdAndFileId()
	{
		$sourceId = $this->insertedData['insertedData']['source_id'];
		$fileId = $this->insertedData['insertedData']['data_file_id'];

		$dbAdapter = new EAVAdapter();
		$result = $dbAdapter->ReadValueFrequenciesBySourceIdAndFileId($sourceId, $fileId);

		$this->assertIsArray($result);
		foreach ($result as $row) {
			$this->assertObjectHasProperty('value_id', $row);
			$this->assertObjectHasProperty('frequency', $row);
		}
	}

	public function testRecordsExistBySourceId()
	{
		$sourceId = $this->insertedData['insertedData']['source_id'];
		$attributeIds = [$this->insertedData['insertedData']['attribute_id']];
		$indexed = true;

		$dbAdapter = new EAVAdapter();
		$result = $dbAdapter->RecordsExistBySourceId($sourceId, $attributeIds, $indexed);

		$this->assertTrue($result);
	}

	public function testReadLastIdBySubjectId()
	{
		$subjectId = $this->insertedData['insertedData']['subject_id'];

		$dbAdapter = new EAVAdapter();
		$result = $dbAdapter->ReadLastIdBySubjectId($subjectId);

		$this->assertIsInt($result);
		$this->assertGreaterThanOrEqual(-1, $result);
	}

	public function testReadLastIdByGroupIdAndSubjectId()
	{
		$subjectId = $this->insertedData['insertedData']['subject_id'];
		$groupId = $this->insertedData['insertedData']['group_id'];

		$dbAdapter = new EAVAdapter();
		$result = $dbAdapter->ReadLastIdByGroupIdAndSubjectId($subjectId, $groupId);

		$this->assertIsInt($result);
		$this->assertGreaterThanOrEqual(0, $result);
	}

    public function testUpdateIndexedBySourceIdAndAttributeIds()
    {
		$sourceId = $this->insertedData['insertedData']['source_id'];
		$attributeIds = [1, 2, 3];

		$dbAdapter = new EAVAdapter();
		$result = $dbAdapter->UpdateIndexedBySourceIdAndAttributeIds($sourceId, $attributeIds);

		$this->assertTrue($result);
    }

	public function testReadUniqueGroupIdsAndSubjectIdsBySourceIdAndAttributeIds()
	{
		$sourceId = $this->insertedData['insertedData']['source_id'];
		$attributeIds = [1, 2, 3];
		$limit = 10;
		$offset = 0;
		$unindexedOnly = true;

		$dbAdapter = new EAVAdapter();
		$result = $dbAdapter->ReadUniqueGroupIdsAndSubjectIdsBySourceIdAndAttributeIds($sourceId, $attributeIds, $limit, $offset, $unindexedOnly);

		$this->assertIsArray($result);

		foreach ($result as $row) {
			$this->assertObjectHasProperty('group_id', $row);
			$this->assertObjectHasProperty('subject_id', $row);
			$this->assertObjectHasProperty('data_file_id', $row);
		}
	}

	public function testCountUniqueSubjectIdsBySourceIdAndAttributeIds()
	{
		$sourceId = $this->insertedData['insertedData']['source_id'];
		$attributeIds = [1, 2, 3];
		$unindexedOnly = true;

		$dbAdapter = new EAVAdapter();
		$result = $dbAdapter->CountUniqueSubjectIdsBySourceIdAndAttributeIds($sourceId, $attributeIds, $unindexedOnly);

		$this->assertIsInt($result);
		$this->assertGreaterThanOrEqual(0, $result);
	}
}
