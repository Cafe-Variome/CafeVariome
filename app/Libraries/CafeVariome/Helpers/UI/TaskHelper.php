<?php namespace App\Libraries\CafeVariome\Helpers\UI;

/**
 * TaskHelper.php
 * Created 14/10/2021
 *
 * This class offers helper functions for tasks in the user interface.
 * @author Mehdi Mehtarizadeh
 */

class TaskHelper
{

	public static function GetTaskType(int $type): string
	{
		switch ($type)
		{
			case TASK_TYPE_FILE_PROCESS:
				return 'File Process';
			case TASK_TYPE_FILE_PROCESS_BATCH:
				return 'File Process Batch';
			case TASK_TYPE_SOURCE_INDEX_ELASTICSEARCH:
				return 'Elasticsearch Index';
			case TASK_TYPE_SOURCE_INDEX_NEO4J:
				return 'Neo4J Index';
			case TASK_TYPE_SOURCE_INDEX_USER_INTERFACE:
				return 'User Interface Index';
		}

		return 'Undefined';
	}

	public static function GetTaskError(int $error): ?string
	{
		switch ($error)
		{
			case TASK_ERROR_NO_ERROR:
				return null;
			case TASK_ERROR_RUNTIME_ERROR:
				return 'Runtime Error';
			case TASK_ERROR_DATA_FILE_ID_NULL:
				return 'No data file ID given';
			case TASK_ERROR_PIPELINE_ID_NULL:
				return 'No pipeline ID given';
			case TASK_ERROR_SOURCE_ID_NULL:
				return 'No source ID given';
			case TASK_ERROR_DATA_FILE_NULL:
				return 'No data file found.';
			case TASK_ERROR_PIPELINE_NULL:
				return 'No pipeline found';
			case TASK_ERROR_DUPLICATE:
				return 'Duplicate task';
			case TASK_ERROR_DATA_FILE_NOT_READ:
				return 'Data file could not be read.';
			case TASK_ERROR_DATA_FILE_NOT_SAVED:
				return 'Data file could not be saved.';
		}

		return 'Undefined';
	}

	public static function GetTaskStatus(int $status)
	{
		switch($status)
		{
			case TASK_STATUS_CREATED:
				return 'Created';
			case TASK_STATUS_STARTED:
				return 'Started';
			case TASK_STATUS_PROCESSING:
				return 'Processing';
			case TASK_STATUS_FINISHED:
				return 'Finished';
			case TASK_STATUS_FAILED:
				return 'Failed';
			case TASK_STATUS_CENCELLED:
				return 'Cancelled';
		}

		return 'Undefined';
	}
}
