<?php namespace App\Libraries\CafeVariome\Helpers\UI;

/**
 * AttributeHelper.php
 * Created 15/09/2021
 *
 * This class offers helper functions for attributes in the user interface.
 * @author Mehdi Mehtarizadeh
 */

class AttributeHelper
{
	public static function getAttributeType(int $type): string
	{
		switch ($type){
			case ATRRIBUTE_TYPE_UNDEFINED:
				return 'Undefined';
			case ATRRIBUTE_TYPE_STRING:
				return 'String';
			case ATRRIBUTE_TYPE_NUMERIC_REAL:
				return 'Real Number';
			case ATRRIBUTE_TYPE_NUMERIC_INTEGER:
				return 'Integer';
			case ATRRIBUTE_TYPE_NUMERIC_NATURAL:
				return 'Natural Number';
		}
		return 'Undefined';
	}

	public static function getAttributeStorageLocation(int $storage_location): string
	{
		switch ($storage_location){
			case ATRRIBUTE_STORAGE_UNDEFINED:
				return 'Undefined';
			case ATRRIBUTE_STORAGE_ELASTICSEARCH:
				return 'Elasticsearch';
			case ATRRIBUTE_STORAGE_NEO4J:
				return 'Neo4J';
		}
		return 'Undefined';
	}
}
