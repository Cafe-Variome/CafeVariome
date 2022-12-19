<?php namespace App\Libraries\CafeVariome\Net;

/**
 * QueryNetworkInterface.php
 *
 * Created: 06/12/2022
 *
 * @author Mehdi Mehtarizadeh
 *
 * This class sends out Beacon queries to endpoints.
 */

class BeaconQueryInterface extends AbstractNetworkInterface
{
	public function __construct(string $targetUri) {
		parent::__construct($targetUri);

		$curlOptions = [CURLOPT_RETURNTRANSFER => TRUE
			//,CURLOPT_HTTPHEADER => ['Expect:'] //Removing Expect header results in speed-up on some LAMPs running with CENTOS
		];

		$this->networkAdapter = new cURLAdapter(null, $curlOptions);

		if ($this->networkAdapterConfig->useProxy) {
			$proxyDetails = $this->networkAdapterConfig->proxyDetails;
			$this->configureProxy($proxyDetails);
		}
	}

	protected function configureProxy(array $proxyConfig)
	{
		$this->networkAdapter->setOption(CURLOPT_FOLLOWLOCATION, true);
		$this->networkAdapter->setOption(CURLOPT_HTTPPROXYTUNNEL, 1);
		$this->networkAdapter->setOption(CURLOPT_PROXY, $proxyConfig['hostname']);
		$this->networkAdapter->setOption(CURLOPT_PROXYPORT, $proxyConfig['port']);

		if ($proxyConfig['username'] != '' && $proxyConfig['password'] != '') {
			$this->networkAdapter->setOption(CURLOPT_PROXYUSERPWD, $proxyConfig['username'] . ':' . $proxyConfig['password']);
		}
	}

	public function query(string $query, int $network_key, string $authentication_url, string $token = null)
	{
//		$this->networkAdapter->setOption(CURLOPT_HTTPHEADER, [
//			//"auth-token: $token", "authentication_url: $authentication_url"
//		]);
		$this->adapterw('', [$query]);
		$response = $this->networkAdapter->Send();
		return $this->processResponse($response);
	}

	protected function adapterw(string $uriTail, array $data)
	{
		$this->networkAdapter->setOption(CURLOPT_URL, $this->serverURI . $uriTail);
		$this->networkAdapter->setOption(CURLOPT_POST, true);
		$this->networkAdapter->setOption(CURLOPT_POSTFIELDS, $data);
	}

	protected function processResponse($response)
	{
		$responseObj = json_decode($response);

		if ($responseObj == null)
		{
			$responseObj = new \StdClass();
			$responseObj->status = false;
		}

		$responseObj->status = true;

		return $responseObj;
	}
}
