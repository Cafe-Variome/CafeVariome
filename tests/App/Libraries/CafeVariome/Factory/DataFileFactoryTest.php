<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\DataFile;
use App\Libraries\CafeVariome\Entities\NullEntity;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 * @covers \App\Libraries\CafeVariome\Entities\DataFile
 * @covers \App\Libraries\CafeVariome\Factory\DataFileFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class DataFileFactoryTest extends TestCase
{
    public function testGetInstanceFromParameters()
    {
		$dataFile = (new DataFileFactory())->GetInstanceFromParameters(
			uniqid(), uniqid(), 1024.1024, time(), 1, 2, 3, DATA_FILE_STATUS_UPLOADED
		);
		$this->assertIsObject($dataFile);
		$this->assertInstanceOf(DataFile::class, $dataFile);
    }

    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->name = uniqid();
		$object->disk_name = uniqid();
		$object->size = 1024.1024;
		$object->upload_date = time();
		$object->record_count = 1;
		$object->user_id = 2;
		$object->source_id = 3;
		$object->status = DATA_FILE_STATUS_UPLOADED;
		$dataFile = (new DataFileFactory())->GetInstance($object);

		$this->assertIsObject($dataFile);
		$this->assertInstanceOf(DataFile::class, $dataFile);

		$emptyObject = new \stdClass();
		$nullEntity = (new DataFileFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
    }
}
