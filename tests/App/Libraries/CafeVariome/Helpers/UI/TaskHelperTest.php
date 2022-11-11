<?php namespace Libraries\CafeVariome\Helpers\UI;

/**
 * TaskHelperTest.php
 * Created 1/11/2022
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Helpers\UI\TaskHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Helpers\UI\TaskHelper
 */
class TaskHelperTest extends TestCase
{

    public function testGetTaskError()
    {
		$this->assertEquals('Undefined', TaskHelper::GetTaskError(-1));
		$this->assertEquals(null, TaskHelper::GetTaskError(TASK_ERROR_NO_ERROR));
		$this->assertEquals('Runtime Error', TaskHelper::GetTaskError(TASK_ERROR_RUNTIME_ERROR));
		$this->assertEquals('No data file ID given', TaskHelper::GetTaskError(TASK_ERROR_DATA_FILE_ID_NULL));
		$this->assertEquals('No pipeline ID given', TaskHelper::GetTaskError(TASK_ERROR_PIPELINE_ID_NULL));
		$this->assertEquals('No source ID given', TaskHelper::GetTaskError(TASK_ERROR_SOURCE_ID_NULL));
		$this->assertEquals('No data file found', TaskHelper::GetTaskError(TASK_ERROR_DATA_FILE_NULL));
		$this->assertEquals('No pipeline found', TaskHelper::GetTaskError(TASK_ERROR_PIPELINE_NULL));
		$this->assertEquals('Duplicate task', TaskHelper::GetTaskError(TASK_ERROR_DUPLICATE));
		$this->assertEquals('Data file could not be read.', TaskHelper::GetTaskError(TASK_ERROR_DATA_FILE_NOT_READ));
		$this->assertEquals('Data file could not be saved.', TaskHelper::GetTaskError(TASK_ERROR_DATA_FILE_NOT_SAVED));
    }

    public function testGetTaskType()
    {
		$this->assertEquals('Undefined', TaskHelper::GetTaskType(-1));
		$this->assertEquals('File Process', TaskHelper::GetTaskType(TASK_TYPE_FILE_PROCESS));
		$this->assertEquals('File Process Batch', TaskHelper::GetTaskType(TASK_TYPE_FILE_PROCESS_BATCH));
		$this->assertEquals('Elasticsearch Index', TaskHelper::GetTaskType(TASK_TYPE_SOURCE_INDEX_ELASTICSEARCH));
		$this->assertEquals('Neo4J Index', TaskHelper::GetTaskType(TASK_TYPE_SOURCE_INDEX_NEO4J));
		$this->assertEquals('User Interface Index', TaskHelper::GetTaskType(TASK_TYPE_SOURCE_INDEX_USER_INTERFACE));
    }

    public function testGetTaskStatus()
    {
		$this->assertEquals('Undefined', TaskHelper::GetTaskStatus(-1));
		$this->assertEquals('Created', TaskHelper::GetTaskStatus(TASK_STATUS_CREATED));
		$this->assertEquals('Started', TaskHelper::GetTaskStatus(TASK_STATUS_STARTED));
		$this->assertEquals('Processing', TaskHelper::GetTaskStatus(TASK_STATUS_PROCESSING));
		$this->assertEquals('Finished', TaskHelper::GetTaskStatus(TASK_STATUS_FINISHED));
		$this->assertEquals('Failed', TaskHelper::GetTaskStatus(TASK_STATUS_FAILED));
		$this->assertEquals('Cancelled', TaskHelper::GetTaskStatus(TASK_STATUS_CENCELLED));
    }
}
