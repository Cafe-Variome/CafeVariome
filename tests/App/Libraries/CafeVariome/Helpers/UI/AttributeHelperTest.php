<?php namespace App\Libraries\CafeVariome\Helpers\UI;

/**
 * AttributeHelperTest.php
 * Created 1/11/2022
 * @author Mehdi Mehtarizadeh
 */

use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Helpers\UI\AttributeHelper
 */
class AttributeHelperTest extends TestCase
{

    public function testGetAttributeStorageLocation()
    {
		$this->assertEquals('Undefined', AttributeHelper::GetAttributeStorageLocation(ATTRIBUTE_STORAGE_UNDEFINED));
		$this->assertEquals('Elasticsearch', AttributeHelper::GetAttributeStorageLocation(ATTRIBUTE_STORAGE_ELASTICSEARCH));
		$this->assertEquals('Neo4J', AttributeHelper::GetAttributeStorageLocation(ATTRIBUTE_STORAGE_NEO4J));
		$this->assertEquals('Undefined', AttributeHelper::GetAttributeStorageLocation(-1));
    }

    public function testGetAttributeType()
    {
		$this->assertEquals('Undefined', AttributeHelper::GetAttributeType(ATTRIBUTE_TYPE_UNDEFINED));
		$this->assertEquals('String', AttributeHelper::GetAttributeType(ATTRIBUTE_TYPE_STRING));
		$this->assertEquals('Real Number', AttributeHelper::GetAttributeType(ATTRIBUTE_TYPE_NUMERIC_REAL));
		$this->assertEquals('Integer', AttributeHelper::GetAttributeType(ATTRIBUTE_TYPE_NUMERIC_INTEGER));
		$this->assertEquals('Natural Number', AttributeHelper::GetAttributeType(ATTRIBUTE_TYPE_NUMERIC_NATURAL));
		$this->assertEquals('Ontology Term', AttributeHelper::GetAttributeType(ATTRIBUTE_TYPE_ONTOLOGY_TERM));
		$this->assertEquals('Undefined', AttributeHelper::GetAttributeType(-1));
    }
}
