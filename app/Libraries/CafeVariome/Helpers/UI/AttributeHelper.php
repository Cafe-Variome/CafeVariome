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
	public static function GetAttributeType(int $type): string
	{
		switch ($type)
		{
			case ATTRIBUTE_TYPE_UNDEFINED:
				return 'Undefined';
			case ATTRIBUTE_TYPE_STRING:
				return 'String';
			case ATTRIBUTE_TYPE_NUMERIC_REAL:
				return 'Real Number';
			case ATTRIBUTE_TYPE_NUMERIC_INTEGER:
				return 'Integer';
			case ATTRIBUTE_TYPE_NUMERIC_NATURAL:
				return 'Natural Number';
			case ATTRIBUTE_TYPE_ONTOLOGY_TERM:
				return 'Ontology Term';
		}
		return 'Undefined';
	}

	public static function GetAttributeStorageLocation(int $storage_location): string
	{
		switch ($storage_location)
		{
			case ATTRIBUTE_STORAGE_UNDEFINED:
				return 'Undefined';
			case ATTRIBUTE_STORAGE_ELASTICSEARCH:
				return 'Elasticsearch';
			case ATTRIBUTE_STORAGE_NEO4J:
				return 'Neo4J';
		}
		return 'Undefined';
	}
}
