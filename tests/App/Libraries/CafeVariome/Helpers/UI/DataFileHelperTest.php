<?php namespace Libraries\CafeVariome\Helpers\UI;

use App\Libraries\CafeVariome\Helpers\UI\DataFileHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Helpers\UI\DataFileHelper
 */
class DataFileHelperTest extends TestCase
{
	public function testGetDataFileStatus()
	{
		$this->assertEquals('Undefined', DataFileHelper::GetDataFileStatus(-1));
		$this->assertEquals('Uploaded', DataFileHelper::GetDataFileStatus(DATA_FILE_STATUS_UPLOADED));
		$this->assertEquals('Imported', DataFileHelper::GetDataFileStatus(DATA_FILE_STATUS_IMPORTED));
		$this->assertEquals('Processing', DataFileHelper::GetDataFileStatus(DATA_FILE_STATUS_PROCESSING));
		$this->assertEquals('Processed', DataFileHelper::GetDataFileStatus(DATA_FILE_STATUS_PROCESSED));
		$this->assertEquals('File Missing', DataFileHelper::GetDataFileStatus(DATA_FILE_STATUS_MISSING));
	}
}
