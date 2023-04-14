<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Index;

/**
 * AbstractNetworkIndex.php
 *
 * Created 01/10/2021
 * @author Mehdi Mehtarizadeh
 *
 * This abstract class defines the structure for indexing all sources within a network.
 */

use App\Libraries\CafeVariome\Database\DiscoveryGroupAdapter;
use App\Libraries\CafeVariome\Database\SourceAdapter;
use App\Libraries\CafeVariome\Factory\DiscoveryGroupAdapterFactory;
use App\Libraries\CafeVariome\Factory\SourceAdapterFactory;

abstract class AbstractNetworkIndex
{
	/**
	 * @var int network key to be indexed
	 */
	protected int $networkKey;

	/**
	 * @var DiscoveryGroupAdapter
	 */
	protected DiscoveryGroupAdapter $discoveryGroupAdapter;

	/**
	 * @var SourceAdapter
	 */
	protected SourceAdapter $sourceAdapter;

	/**
	 * @var array source IDs within the network
	 */
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

	/**
	 * @return void populates list of source IDs that are in the network
	 */
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
