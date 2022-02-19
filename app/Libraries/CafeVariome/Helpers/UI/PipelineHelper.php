<?php namespace App\Libraries\CafeVariome\Helpers\UI;

/**
 * PipelineHelper.php
 * Created: 19/02/2022
 * @author Mehdi Mehtarizadeh
 *
 * This class offers helper functions for pipelines in the user interface.
 */

class PipelineHelper
{
	public static function getSubjectIDLocation(int $sid_location): string
	{
		switch ($sid_location)
		{
			case SUBJECT_ID_WITHIN_FILE:
				return 'Attribute in File';
			case SUBJECT_ID_IN_FILE_NAME:
				return 'File Name';
			case SUBJECT_ID_PER_BATCH_OF_RECORDS:
				return 'No Subject ID Given - Assign per Batch of Records in File';
			case SUBJECT_ID_PER_FILE:
				return 'No Subject ID Given - Assign per File';
		}
		return 'Undefined';
	}

	public static function getGrouping(int $grouping): string
	{
		switch ($grouping)
		{
			case GROUPING_COLUMNS_ALL:
				return 'Group Individually';
			case GROUPING_COLUMNS_CUSTOM:
				return 'Customised';
		}
		return 'Undefined';
	}
}
