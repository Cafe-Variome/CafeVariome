<?php namespace App\Libraries\CafeVariome\Net;

use App\Libraries\CafeVariome\Entities\SingleSignOnProvider;

/**
 * NetworkInterface.php
 *
 * Created: 25/05/2022
 *
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 *
 * This class interfaces the software with any openID provider.
 */

class OpenIDNetworkInterface extends AbstractNetworkInterface
{

	public function __construct(string $targetURI, array $proxyOptions = [])
	{
		parent::__construct($targetURI);

		$curlOptions = [CURLOPT_RETURNTRANSFER => TRUE,
			//CURLOPT_HTTPHEADER => ['Expect:'] //Removing Expect header results in speed-up on some LAMPs running with CENTOS
		];

		$this->networkAdapter = new cURLAdapter(null, $curlOptions);

		if (count($proxyOptions) > 0)
		{
			$this->configureProxy($proxyOptions);
		}
	}

	protected function configureProxy(array $proxyConfig)
	{
		$this->networkAdapter->setOption(CURLOPT_FOLLOWLOCATION, true);
		$this->networkAdapter->setOption(CURLOPT_HTTPPROXYTUNNEL, 1);
		$this->networkAdapter->setOption(CURLOPT_PROXY, $proxyConfig['hostname']);
		$this->networkAdapter->setOption(CURLOPT_PROXYPORT, $proxyConfig['port']);

		if ($proxyConfig['username'] != '' && $proxyConfig['password'] != '')
		{
			$this->networkAdapter->setOption(CURLOPT_PROXYUSERPWD, $proxyConfig['username'] . ':' . $proxyConfig['password']);
		}
	}

	public function GetMetaData(?string $wellKnownEndpoint)
	{
		if (is_null($wellKnownEndpoint))
		{
			$this->adapterw('.well-known/openid-configuration', []);
		}
		else
		{
			$this->adapterw($wellKnownEndpoint, []);
		}

		$response = $this->networkAdapter->Send();
		return $this->processResponse($response);
	}

	public function GetToken(string $params, string $credential)
	{
		$this->networkAdapter->setOption(CURLOPT_URL, $this->serverURI);
		$this->networkAdapter->setOption(CURLOPT_HTTPHEADER, [
			'content-type: application/x-www-form-urlencoded',
			'Authorization: Basic ' . $credential
		]);

		$this->networkAdapter->setOption(CURLOPT_POST, true);
		$this->networkAdapter->setOption(CURLOPT_POSTFIELDS, $params);

		$response = $this->networkAdapter->Send();
		return $this->processResponse($response);
	}

	public function GetResourceOwner(string $token)
	{
		$this->networkAdapter->setOption(CURLOPT_URL, $this->serverURI);
		$this->networkAdapter->setOption(CURLOPT_HTTPHEADER, [
			'content-type: application/x-www-form-urlencoded',
			'Authorization: Bearer ' . $token
		]);

		$this->networkAdapter->setOption(CURLOPT_POST, true);
		$this->networkAdapter->setOption(CURLOPT_POSTFIELDS, $token);

		$response = $this->networkAdapter->Send();
		return $this->processResponse($response);
	}

	protected function adapterw(string $uriTail, array $data)
	{
		$this->networkAdapter->setOption(CURLOPT_URL, $this->serverURI . $uriTail);
		//$this->networkAdapter->setOption(CURLOPT_POSTFIELDS, $data);
	}

	protected function processResponse($response)
	{
		$responseObj = json_decode($response, true);

		if ($responseObj == Null)
		{
			$responseObj = new \StdClass();
			$responseObj->status = false;
			$responseObj->adapter_error = $this->networkAdapter->GetError();
		}

		return $responseObj;
	}
}
