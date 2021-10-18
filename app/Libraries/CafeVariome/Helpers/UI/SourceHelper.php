<?php namespace App\Libraries\CafeVariome\Helpers\UI;

/**
 * SourceHelper.php
 * Created 04/10/2021
 *
 * This class offers helper functions for sources in the user interface.
 * @author Mehdi Mehtarizadeh
 */

class SourceHelper
{
	public static function getElasticsearchIndexStatus(int $status)
	{
		switch ($status){
			case ELASTICSEARCH_INDEX_STATUS_UNKNOWN:
				return 'Unknown';
			case ELASTICSEARCH_INDEX_STATUS_CREATED:
				return 'Created';
			case ELASTICSEARCH_INDEX_STATUS_NOT_CREATED:
				return 'Not Created';
		}
		return 'Undefined';
	}

	public static function getElasticsearchDataStatus(int $status)
	{
		switch ($status){
			case ELASTICSEARCH_DATA_STATUS_UNKNOWN:
				return 'Unknown';
			case ELASTICSEARCH_DATA_STATUS_NOT_INDEXED:
				return 'Not Indexed';
			case ELASTICSEARCH_DATA_STATUS_FULLY_INDEXED:
				return 'Fully Indexed';
			case ELASTICSEARCH_DATA_STATUS_PARTIALLY_INDEXED:
				return 'Partially Indexed';
			case ELASTICSEARCH_DATA_STATUS_EMPTY:
				return 'Source Empty';
		}
		return 'Undefined';
	}

	public static function getNeo4JIndexStatus(int $status)
	{
		switch ($status){
			case NEO4J_INDEX_STATUS_UNKNOWN:
				return 'Unknown';
			case NEO4J_INDEX_STATUS_CREATED:
				return 'Created';
			case NEO4J_INDEX_STATUS_NOT_CREATED:
				return 'Not Created';
		}
		return 'Undefined';
	}

	public static function getNeo4JDataStatus(int $status)
	{
		switch ($status){
			case NEO4J_DATA_STATUS_UNKNOWN:
				return 'Unknown';
			case NEO4J_DATA_STATUS_NOT_INDEXED:
				return 'Not Indexed';
			case NEO4J_DATA_STATUS_FULLY_INDEXED:
				return 'Fully Indexed';
			case NEO4J_DATA_STATUS_PARTIALLY_INDEXED:
				return 'Partially Indexed';
			case NEO4J_DATA_STATUS_EMPTY:
				return 'Source Empty';
		}
		return 'Undefined';
	}

	public static function getUserInterfaceIndexStatus(int $status)
	{
		switch ($status){
			case USER_INTERFACE_INDEX_STATUS_UNKNOWN:
				return 'Unknown';
			case USER_INTERFACE_INDEX_STATUS_CREATED:
				return 'Created';
			case USER_INTERFACE_INDEX_STATUS_NOT_CREATED:
				return 'Not Created';
		}
		return 'Undefined';
	}

	public static function formatSize(int $size): string
	{
		if ($size < 0){
			return "Invalid size";
		}
		if ($size < 1024){
			return $size . " Bytes";
		}
		else if($size < 1048576){
			return round($size/1024, 2) . " KB";
		}
		else if($size < 1073741824){
			return round($size/1048576, 2) . " MB";
		}
		else {
			return round($size/1099511627776, 2) . " GB";
		}
		return "Invalid size";

	}
}
