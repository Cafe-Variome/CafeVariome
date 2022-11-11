<?php namespace App\Libraries\CafeVariome\Helpers\UI;

/**
 * PipelineHelperTest.php
 * Created 1/11/2022
 * @author Mehdi Mehtarizadeh
 */

use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Helpers\UI\PipelineHelper
 */
class PipelineHelperTest extends TestCase
{

    public function testGetGrouping()
    {
		$this->assertEquals('Undefined', PipelineHelper::GetGrouping(-1));
		$this->assertEquals('Customised', PipelineHelper::GetGrouping(GROUPING_COLUMNS_CUSTOM));
		$this->assertEquals('Group Individually', PipelineHelper::GetGrouping(GROUPING_COLUMNS_ALL));
    }

    public function testGetSubjectIDLocation()
    {
		$this->assertEquals('Undefined', PipelineHelper::GetSubjectIDLocation(-1));
		$this->assertEquals('Attribute in File', PipelineHelper::GetSubjectIDLocation(SUBJECT_ID_WITHIN_FILE));
		$this->assertEquals('File Name', PipelineHelper::GetSubjectIDLocation(SUBJECT_ID_IN_FILE_NAME));
		$this->assertEquals('No Subject ID Given - Assign per Batch of Records in File', PipelineHelper::GetSubjectIDLocation(SUBJECT_ID_PER_BATCH_OF_RECORDS));
		$this->assertEquals('No Subject ID Given - Assign per File', PipelineHelper::GetSubjectIDLocation(SUBJECT_ID_PER_FILE));
		$this->assertEquals('No Subject ID Given - Assign by Expansion of Column(s)', PipelineHelper::GetSubjectIDLocation(SUBJECT_ID_BY_EXPANSION_ON_COLUMNS));
    }

    public function testGetExpansionPolicy()
    {
		$this->assertEquals('Undefined', PipelineHelper::GetExpansionPolicy(-1));
		$this->assertEquals('Individual', PipelineHelper::GetExpansionPolicy(SUBJECT_ID_EXPANDSION_POLICY_INDIVIDUAL));
		$this->assertEquals('Choose Maximum Cell Value', PipelineHelper::GetExpansionPolicy(SUBJECT_ID_EXPANDSION_POLICY_MAXIMUM));
		$this->assertEquals('Choose Minimum Cell Value', PipelineHelper::GetExpansionPolicy(SUBJECT_ID_EXPANDSION_POLICY_MINIMUM));
    }
}
