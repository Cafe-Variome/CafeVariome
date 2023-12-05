<?php namespace App\Libraries\CafeVariome\Net;

use App\Libraries\CafeVariome\Factory\AuthenticatorFactory;
use App\Libraries\CafeVariome\Factory\SingleSignOnProviderAdapterFactory;

/**
 * NetworkInterface.php
 *
 * Created: 31/10/2019
 *
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 * @auhtor Sadegh Abadijou
 *
 * This class interfaces the software with the authentication server and handles data sent/received to the server.
 * It is mainly for CRUD operation of shared entities.
 */

class NetworkInterface extends AbstractNetworkInterface
{
	protected $installation_key;

	public function __construct(string $targetUri = '', int $providerID = null) {
		parent::__construct($targetUri, $providerID);

		$this->installation_key = $this->setting->getInstallationKey();

		$this->serverURI = $this->setting->getAuthServerUrl();

		$this->provider = (new SingleSignOnProviderAdapterFactory())->GetInstance()->Read($providerID);

		$curlOptions = [CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_HTTPHEADER => ['Expect:'] //Removing Expect header results in speed-up on some LAMPs running with CENTOS];
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

	public function CreateNetwork(array $data)
	{
		$this->adapterw('NetworkApi/createNetwork', ['installation_key' => $this->installation_key,
			'network_name' => $data['network_name'], 'network_type' => $data['network_type'],
			'network_threshold' => $data['network_threshold'], 'network_status' => $data['network_status']]);

		$response = $this->networkAdapter->Send();
		return $this->processResponse($response);
	}

	public function AddInstallationToNetwork(array $data)
	{
		$this->adapterw('NetworkApi/addInstallationToNetwork', ['installation_key' => $this->installation_key,
			'network_key' => $data['network_key']]);

		$response = $this->networkAdapter->Send();
		return $this->processResponse($response);
	}

	public function GetNetworksByInstallationKey(string $installation_key)
	{
		$this->adapterw('NetworkApi/getNetworksByInstallationKey', ['installation_key' => $installation_key]);

		$response = $this->networkAdapter->Send();
		return $this->processResponse($response);
	}

	public function GetNetwork(int $network_key)
	{
		$this->adapterw('NetworkApi/getNetwork', ['installation_key' => $this->installation_key, 'network_key' => $network_key]);

		$response = $this->networkAdapter->Send();
		return $this->processResponse($response);
	}

	public function GetNetworkThreshold(int $network_key)
	{
		$this->adapterw('NetworkApi/getNetworkThreshold', ['installation_key' => $this->installation_key,
			'network_key' => $network_key]);

		$response = $this->networkAdapter->Send();
		return $this->processResponse($response);
	}

	public function SetNetworkThreshold(int $network_key, int $network_threshold)
	{
		$this->adapterw('NetworkApi/setNetworkThreshold', ['installation_key' => $this->installation_key,
			'network_key' => $network_key, 'network_threshold' => $network_threshold]);

		$response = $this->networkAdapter->Send();
		return $this->processResponse($response);
	}

	public function LeaveNetwork(int $network_key)
	{
		$this->adapterw('NetworkApi/leaveNetwork', ['installation_key' => $this->installation_key,
			'network_key' => $network_key]);

		$response = $this->networkAdapter->Send();
		return $this->processResponse($response);
	}

	public function GetAvailableNetworks()
	{
		$this->adapterw('NetworkApi/getAvailableNetworks', ['installation_key' => $this->installation_key]);

		$response = $this->networkAdapter->Send();
		return $this->processResponse($response);
	}

	public function RequestToJoinNetwork(int $network_key, string $email, string $justification)
	{
		$this->adapterw('NetworkApi/requestToJoinNetwork', ['installation_key' => $this->installation_key,
			'network_key' => strval($network_key), 'email' => $email,
			'justification' => $justification]);

		$response = $this->networkAdapter->Send();
		return $this->processResponse($response);
	}

	public function AcceptRequest(string $token)
	{
		$this->adapterw('NetworkApi/acceptRequest', ['installation_key' => $this->installation_key,
			'token' => $token]);

		$response = $this->networkAdapter->Send();
		return $this->processResponse($response);
	}

	public function DenyRequest(string $token)
	{
		$this->adapterw('NetworkApi/denyRequest', ['installation_key' => $this->installation_key,
			'token' => $token]);

		$response = $this->networkAdapter->Send();
		return $this->processResponse($response);
	}

	public function GetInstallationsByNetworkKey(int $network_key)
	{
		$this->adapterw('NetworkApi/getInstallationsByNetworkKey', ['installation_key' => $this->installation_key,
			'network_key' => $network_key]);

		$response = $this->networkAdapter->Send();
		return $this->processResponse($response);
	}

	protected function adapterw(string $uriTail, array $data)
	{

		$this->networkAdapter->setOption(CURLOPT_URL, $this->serverURI . $uriTail);
		$this->networkAdapter->setOption(CURLOPT_POST, true);
		$this->networkAdapter->setOption(CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $this->getAccessToken()));
		$this->networkAdapter->setOption(CURLOPT_POSTFIELDS, $data);
	}

	public function getAccessToken()
	{
		$authenticatorFactory = new AuthenticatorFactory();

		$authenticator = $authenticatorFactory->GetInstance($this->provider);

		return $authenticator->getServiceAccessToken();

	}
}
