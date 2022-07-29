<?php namespace App\Libraries\CafeVariome\Helpers\UI;

/**
 * DataFileHelper.php
 * Created 29/07/2022
 *
 * This class offers helper functions for datafiles in the user interface.
 * @author Mehdi Mehtarizadeh
 */

class DataFileHelper
{
	public static function GetDataFileStatus(int $status): string
	{
		switch ($status)
		{
			case DATA_FILE_STATUS_UPLOADED:
				return 'Uploaded';
			case DATA_FILE_STATUS_IMPORTED:
				return 'Imported';
			case DATA_FILE_STATUS_PROCESSING:
				return 'Procesing';
			case DATA_FILE_STATUS_PROCESSED:
				return 'Processed';
			case DATA_FILE_STATUS_MISSING:
				return 'File Missing';
		}
		return 'Undefined';
	}
}
