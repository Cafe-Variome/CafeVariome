<?php

namespace App\Libraries\CafeVariome\Core\DataPipeLine\Index;

use App\Models\Source;

abstract class AbstractNetworkIndex
{

	protected int $networkKey;
	protected Source $sourceModel;
	protected array $sources;

	public function __construct(int $network_key)
	{
		$this->networkKey = $network_key;
		$this->sourceModel = new \App\Models\Source();
		$this->sources = [];
		$this->getSourcesInNetwork();
	}

	abstract public function IndexNetwork();

	private function getSourcesInNetwork()
	{
		$sources = $this->sourceModel->getSourcesByNetwork($this->networkKey); // Get sources that are in the network
		foreach ($sources as $source) {
			if (!in_array($source['source_id'], $this->sources)){
				array_push($this->sources, $source['source_id']);
			}
		}
	}
}
