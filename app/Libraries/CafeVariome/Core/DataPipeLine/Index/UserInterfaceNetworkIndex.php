<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Index;

use App\Libraries\CafeVariome\Core\IO\FileSystem\SysFileMan;
use App\Libraries\CafeVariome\Factory\DiscoveryGroupAdapterFactory;
use App\Libraries\CafeVariome\Net\NetworkInterface;
use App\Libraries\CafeVariome\Net\QueryNetworkInterface;

class UserInterfaceNetworkIndex extends AbstractNetworkIndex
{
	public function __construct(int $network_key)
	{
		parent::__construct($network_key);
	}

    public function IndexNetwork()
    {
		$basePath = FCPATH . USER_INTERFACE_INDEX_DIR;
		$fileMan = new SysFileMan($basePath);

		$sourcesIndexData = [
			'source_ids' => [],
			'attributes_values' => [],
			'attributes_display_names' => [],
			'values_display_names' => [],
		];

		foreach ($this->sourceIds as $source_id)
		{
			$indexName = $this->getSourceUIIndexName($source_id);

			if ($fileMan->Exists($indexName)){
				$indexData = json_decode($fileMan->Read($indexName), true);
				if (
					is_array($indexData) &&
					array_key_exists('source_id', $indexData) &&
					array_key_exists('attributes_values', $indexData) &&
					array_key_exists('attributes_display_names', $indexData) &&
					array_key_exists('values_display_names', $indexData)
				)
				{
					$attributes_values = $indexData['attributes_values'];
					$attributes_display_names = $indexData['attributes_display_names'];
					$values_display_names = $indexData['values_display_names'];

					if (!in_array($source_id, $sourcesIndexData['source_ids'])){
						array_push($sourcesIndexData['source_ids'], $source_id);
					}

					foreach ($attributes_values as $attribute => $values) {
						if (array_key_exists($attribute, $sourcesIndexData['attributes_values'])) {
							foreach ($values as $value) {
								if (!in_array($value, $sourcesIndexData['attributes_values'][$attribute])) {
									array_push($sourcesIndexData['attributes_values'][$attribute], $value);
								}
							}
						}
						else {
							$sourcesIndexData['attributes_values'][$attribute] = $values;
						}
					}

					foreach ($attributes_display_names as $attribute => $display_name) {
						if (array_key_exists($attribute, $sourcesIndexData['attributes_display_names'])) {
							if (!in_array($display_name, $sourcesIndexData['attributes_display_names'][$attribute])) {
								array_push($sourcesIndexData['attributes_display_names'][$attribute], $display_name);
							}
						}
						else{
							$sourcesIndexData['attributes_display_names'][$attribute] = [$display_name];
						}
					}

					foreach ($values_display_names as $value => $display_names) {
						if (array_key_exists($value, $sourcesIndexData['values_display_names'])) {
							foreach ($display_names as $display_name) {
								if (!in_array($display_name, $sourcesIndexData['values_display_names'][$value])) {
									array_push($sourcesIndexData['values_display_names'][$value], $display_name);
								}
							}
						}
						else {
							$sourcesIndexData['values_display_names'][$value] = $display_names;
						}
					}
				}
			}
		}

		$fileMan->Write($this->networkKey . '.json', json_encode($sourcesIndexData));
	}

	public function IndexNetworkInstallations()
	{
		$basePath = FCPATH . USER_INTERFACE_INDEX_DIR;
		$discoveryGroupAdapter = (new DiscoveryGroupAdapterFactory())->GetInstance();
		$fileMan = new SysFileMan($basePath);
		$networkInterface = new NetworkInterface();
		$response = $networkInterface->GetInstallationsByNetworkKey($this->networkKey);

		$installations = [];

		if ($response->status) {
			$installations = $response->data;
		}

		$UIData = [
			'attributes_values' => [],
			'attributes_display_names' => [],
			'values_display_names' => [],
		];

		$networkIndexed = $fileMan->Exists($this->networkKey . '.json');
		if(!$networkIndexed)
		{
			$this->IndexNetwork();
		}

		$localNetworkData = json_decode($fileMan->Read($this->networkKey . '.json'), true);


		if ($networkIndexed)
		{
			$discoveryGroups = $discoveryGroupAdapter->ReadByNetworkId($this->networkKey);
			$discoveryGroupIds = [];
			foreach ($discoveryGroups as $discoveryGroup)
			{
				$discoveryGroupIds[] = $discoveryGroup->getID();
			}

			$sourceIds = $discoveryGroupAdapter->ReadAssociatedSourceIds($discoveryGroupIds);
			$indexedSourceIds = $localNetworkData['source_ids'];
			if (count($sourceIds) > 0 && is_array($indexedSourceIds))
			{
				$sourceIdsUpdated = false;
				for ($i = 0; $i < count($sourceIds); $i++)
				{
					if (!in_array($sourceIds[$i], $indexedSourceIds))
					{
						// Need to update network index as some sources within the discovery group have been modified or removed
						$sourceIdsUpdated = true;
						break;
					}
				}

				if ($sourceIdsUpdated)
				{
					$this->IndexNetwork();
				}
			}
		}

		$UIData['attributes_values'] = $localNetworkData['attributes_values'];
		$UIData['attributes_display_names'] = $localNetworkData['attributes_display_names'];
		$UIData['values_display_names'] = $localNetworkData['values_display_names'];

		foreach ($installations as $installation) {
			$queryNetInterface = new QueryNetworkInterface($installation->base_url);
			$eavJson = $queryNetInterface->getEAVJSON($this->networkKey, $fileMan->GetModificationTimeStamp($this->networkKey . '_local.json'));
			$status = $eavJson->status;

			if ($status) {
				if ($eavJson->data->modified) {
					$result = $eavJson->data->json;
					$resultArr = json_decode($result, true);
					if (
						is_array($resultArr) &&
						array_key_exists('attributes_values', $resultArr) &&
						array_key_exists('attributes_display_names', $resultArr) &&
						array_key_exists('values_display_names', $resultArr)
					)
					{
						$attributes_values = $resultArr['attributes_values'];
						$attributes_display_names = $resultArr['attributes_display_names'];
						$values_display_names = $resultArr['values_display_names'];

						foreach ($attributes_values as $attribute => $values) {
							if (array_key_exists($attribute, $UIData['attributes_values'])) {
								foreach ($values as $value) {
									if (!in_array($value, $UIData['attributes_values'][$attribute])) {
										array_push($UIData['attributes_values'][$attribute], $value);
									}
								}
							}
							else {
								$UIData['attributes_values'][$attribute] = $values;
							}
						}

						foreach ($attributes_display_names as $attribute => $display_names) {
							foreach ($display_names as $display_name) {
								if (array_key_exists($attribute, $UIData['attributes_display_names'])) {
									if (!in_array($display_name, $UIData['attributes_display_names'][$attribute])) {
										array_push($UIData['attributes_display_names'][$attribute], $display_name);
									}
								}
								else{
									$UIData['attributes_display_names'][$attribute] = [$display_name];
								}
							}
						}

						foreach ($values_display_names as $value => $display_names) {
							if (array_key_exists($value, $UIData['values_display_names'])) {
								foreach ($display_names as $display_name) {
									if (!in_array($display_name, $UIData['values_display_names'][$value])) {
										array_push($UIData['values_display_names'][$value], $display_name);
									}
								}
							}
							else {
								$UIData['values_display_names'][$value] = $display_names;
							}
						}
					}
				}
			}
		}

		if (
			count($UIData['attributes_values']) > 0 &&
			count($UIData['attributes_display_names']) > 0 &&
			count($UIData['values_display_names']) > 0
		)
		{
			$fileMan->Write($this->networkKey . '_local.json', json_encode($UIData, JSON_INVALID_UTF8_SUBSTITUTE));
		}

		if (!$fileMan->Exists($this->networkKey . '_local.json')) {
			$fileMan->Write($this->networkKey . '_local.json', json_encode($UIData, JSON_INVALID_UTF8_SUBSTITUTE));
		}
	}

	public function SourceIndicesUpdated(): bool
	{
		$basePath = FCPATH . USER_INTERFACE_INDEX_DIR;
		$fileMan = new SysFileMan($basePath);
		$networkIndexTimeStamp = $fileMan->GetModificationTimeStamp($this->networkKey . '.json');

		$networkIndex = json_decode($fileMan->Read($this->networkKey . '.json'), true);
		if (array_key_exists('source_ids', $networkIndex)){
			$sourcesIndexed = $networkIndex['source_ids'];
			if (count(array_diff($sourcesIndexed, $this->sourceIds)) > 0){
				return true;
			}
		}

		foreach ($this->sourceIds as $source_id){
			$sourceIndexName = $this->getSourceUIIndexName($source_id);
			if ($fileMan->GetModificationTimeStamp($sourceIndexName) > $networkIndexTimeStamp ){
				return true;
			}
		}

		return false;
	}

	private function getSourceUIIndexName(int $source_id): string
	{
		$uid = $this->sourceAdapter->ReadUID($source_id);
		return $source_id . '_' . $uid . '.json';
	}
}
