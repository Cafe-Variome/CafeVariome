<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Index;

use App\Libraries\CafeVariome\Database\DiscoveryGroupAdapter;
use App\Libraries\CafeVariome\Database\SourceAdapter;
use App\Libraries\CafeVariome\Factory\DiscoveryGroupAdapterFactory;
use App\Libraries\CafeVariome\Factory\SourceAdapterFactory;

abstract class AbstractNetworkIndex
{
	protected int $networkKey;
	protected DiscoveryGroupAdapter $discoveryGroupAdapter;
	protected SourceAdapter $sourceAdapter;
	protected array $sourceIds;

	public function __construct(int $network_key)
	{
		$this->networkKey = $network_key;
		$this->discoveryGroupAdapter = (new DiscoveryGroupAdapterFactory())->GetInstance();
		$this->sourceAdapter = (new SourceAdapterFactory())->GetInstance();
		$this->sourceIds = [];
		$this->getSourcesInNetwork();
	}

	abstract public function IndexNetwork();

	private function getSourcesInNetwork()
	{
		$discoveryGroups = $this->discoveryGroupAdapter->ReadByNetworkId($this->networkKey);
		$discoveryGroupIds = [];

		foreach ($discoveryGroups as $discoveryGroup)
		{
			array_push($discoveryGroupIds, $discoveryGroup->getID());
		}

		$this->sourceIds = $this->discoveryGroupAdapter->ReadAssociatedSourceIds($discoveryGroupIds);
	}
}
