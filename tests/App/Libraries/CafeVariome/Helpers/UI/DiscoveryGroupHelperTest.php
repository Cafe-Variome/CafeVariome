<?php namespace Libraries\CafeVariome\Helpers\UI;

use App\Libraries\CafeVariome\Helpers\UI\DiscoveryGroupHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Helpers\UI\DiscoveryGroupHelper
 */
class DiscoveryGroupHelperTest extends TestCase
{

    public function testGetPolicy()
    {
		$this->assertEquals('Undefined', DiscoveryGroupHelper::GetPolicy(-1));
		$this->assertEquals('Show source existence', DiscoveryGroupHelper::GetPolicy(DISCOVERY_GROUP_POLICY_EXISTENCE));
		$this->assertEquals('Show boolean results based on network threshold', DiscoveryGroupHelper::GetPolicy(DISCOVERY_GROUP_POLICY_BOOLEAN));
		$this->assertEquals('Show number of results', DiscoveryGroupHelper::GetPolicy(DISCOVERY_GROUP_POLICY_COUNT));
		$this->assertEquals('Show list of results with selected attributes', DiscoveryGroupHelper::GetPolicy(DISCOVERY_GROUP_POLICY_LIST_WITH_ATTRIBUTES));
    }
}
