<?php namespace Libraries\CafeVariome\Helpers\UI;

/**
 * SourceHelperTest.php
 * Created 1/11/2022
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Helpers\UI\SourceHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Helpers\UI\SourceHelper
 */
class SourceHelperTest extends TestCase
{

    public function testGetUserInterfaceIndexStatus()
    {
		$this->assertEquals('Undefined', SourceHelper::getUserInterfaceIndexStatus(-1));
		$this->assertEquals('Unknown', SourceHelper::getUserInterfaceIndexStatus(USER_INTERFACE_INDEX_STATUS_UNKNOWN));
		$this->assertEquals('Created', SourceHelper::getUserInterfaceIndexStatus(USER_INTERFACE_INDEX_STATUS_CREATED));
		$this->assertEquals('Not Created', SourceHelper::getUserInterfaceIndexStatus(USER_INTERFACE_INDEX_STATUS_NOT_CREATED));
    }

    public function testGetNeo4JDataStatus()
    {
		$this->assertEquals('Undefined', SourceHelper::getNeo4JDataStatus(-1));
		$this->assertEquals('Unknown', SourceHelper::getNeo4JDataStatus(NEO4J_DATA_STATUS_UNKNOWN));
		$this->assertEquals('Not Indexed', SourceHelper::getNeo4JDataStatus(NEO4J_DATA_STATUS_NOT_INDEXED));
		$this->assertEquals('Fully Indexed', SourceHelper::getNeo4JDataStatus(NEO4J_DATA_STATUS_FULLY_INDEXED));
		$this->assertEquals('Partially Indexed', SourceHelper::getNeo4JDataStatus(NEO4J_DATA_STATUS_PARTIALLY_INDEXED));
		$this->assertEquals('Source Empty', SourceHelper::getNeo4JDataStatus(NEO4J_DATA_STATUS_EMPTY));
    }

    public function testGetElasticsearchIndexStatus()
    {
		$this->assertEquals('Undefined', SourceHelper::getElasticsearchIndexStatus(-1));
		$this->assertEquals('Unknown', SourceHelper::getElasticsearchIndexStatus(ELASTICSEARCH_INDEX_STATUS_UNKNOWN));
		$this->assertEquals('Created', SourceHelper::getElasticsearchIndexStatus(ELASTICSEARCH_INDEX_STATUS_CREATED));
		$this->assertEquals('Not Created', SourceHelper::getElasticsearchIndexStatus(ELASTICSEARCH_INDEX_STATUS_NOT_CREATED));
    }

    public function testGetElasticsearchDataStatus()
    {
		$this->assertEquals('Undefined', SourceHelper::getElasticsearchDataStatus(-1));
		$this->assertEquals('Unknown', SourceHelper::getElasticsearchDataStatus(ELASTICSEARCH_DATA_STATUS_UNKNOWN));
		$this->assertEquals('Not Indexed', SourceHelper::getElasticsearchDataStatus(ELASTICSEARCH_DATA_STATUS_NOT_INDEXED));
		$this->assertEquals('Fully Indexed', SourceHelper::getElasticsearchDataStatus(ELASTICSEARCH_DATA_STATUS_FULLY_INDEXED));
		$this->assertEquals('Partially Indexed', SourceHelper::getElasticsearchDataStatus(ELASTICSEARCH_DATA_STATUS_PARTIALLY_INDEXED));
		$this->assertEquals('Source Empty', SourceHelper::getElasticsearchDataStatus(ELASTICSEARCH_DATA_STATUS_EMPTY));
	}

    public function testGetNeo4JIndexStatus()
    {
		$this->assertEquals('Undefined', SourceHelper::getNeo4JIndexStatus(-1));
		$this->assertEquals('Created', SourceHelper::getNeo4JIndexStatus(NEO4J_INDEX_STATUS_CREATED));
		$this->assertEquals('Not Created', SourceHelper::getNeo4JIndexStatus(NEO4J_INDEX_STATUS_NOT_CREATED));
		$this->assertEquals('Unknown', SourceHelper::getNeo4JIndexStatus(NEO4J_INDEX_STATUS_UNKNOWN));
    }

    public function testGetSourceStatus()
    {
		$this->assertEquals('Undefined', SourceHelper::getSourceStatus(-1));
		$this->assertEquals('Offline', SourceHelper::getSourceStatus(SOURCE_STATUS_OFFLINE));
		$this->assertEquals('Online', SourceHelper::getSourceStatus(SOURCE_STATUS_ONLINE));
    }

    public function testFormatSize()
    {
		$this->assertEquals('Invalid size', SourceHelper::formatSize(-1));
		$this->assertEquals('0 Bytes', SourceHelper::formatSize(0));
		$this->assertEquals('512 Bytes', SourceHelper::formatSize(512));
		$this->assertEquals('1 KB', SourceHelper::formatSize(1025));
		$this->assertEquals('1024 MB', SourceHelper::formatSize(1073741823));
		$this->assertEquals('1 GB', SourceHelper::formatSize(1073741825));
		$this->assertEquals('1024 GB', SourceHelper::formatSize(1099511627777));
    }
}
