<?php namespace App\Libraries\CafeVariome\Auth;

/**
 * Name: OpenIDAuthenticator.php
 * Created: 02/10/2020
 * @author Mehdi Mehtarizadeh
 *
 */

use App\Libraries\CafeVariome\Database\UserAdapter;
use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\CredentialAdapterFactory;
use App\Libraries\CafeVariome\Factory\ProxyServerAdapterFactory;
use App\Libraries\CafeVariome\Factory\ServerAdapterFactory;
use App\Libraries\CafeVariome\Factory\UserAdapterFactory;
use App\Libraries\CafeVariome\Factory\UserFactory;
use App\Libraries\CafeVariome\Helpers\Core\URLHelper;
use App\Libraries\CafeVariome\Net\OpenIDNetworkInterface;
use App\Libraries\CafeVariome\Security\Cryptography;
use App\Libraries\CafeVariome\Net\cURLAdapter;
use App\Libraries\CafeVariome\Entities\SingleSignOnProvider;

class OpenIDAuthenticator
{
    protected SingleSignOnProvider $provider;

    protected array $options;

	protected string $state;

	protected array $scopes;

	protected array $proxyOptions;

	protected UserAdapter $userAdapter;

	protected ?string $lastError;

	protected string $baseURL;

	protected $session;

	protected const AUTHENTICATOR_SESSION = AUTHENTICATOR_SESSION_NAME;
	protected const SSO_RANDOM_STATE_SESSION = SSO_RANDOM_STATE_SESSION_NAME;
	protected const SSO_TOKEN_SESSION = SSO_TOKEN_SESSION_NAME;
	protected const SSO_REFRESH_TOKEN_SESSION = SSO_REFRESH_TOKEN_SESSION_NAME;
	protected const POST_AUTHENTICATION_REDIRECT_URL_SESSION = POST_AUTHENTICATION_REDIRECT_URL_SESSION_NAME;

    public function __construct(SingleSignOnProvider $provider)
	{
		$this->provider = $provider;
		$this->userAdapter = (new UserAdapterFactory())->getInstance();
		$options = [];
		$this->state = '';
		$this->proxyOptions = [];
		$this->lastError = null;
		$this->session =  \Config\Services::session();

		$server = (new ServerAdapterFactory())->getInstance()->Read($provider->server_id);
		$this->baseURL = URLHelper::InsertPort($server->address, $provider->port);

		if ($provider->credential_id != null)
		{
			$credential = (new CredentialAdapterFactory())->getInstance()->Read($provider->credential_id);

			if (!$credential->isNull())
			{
				$options['client_id'] = $credential->username;
				$options['client_secret'] = $credential->password != null ? Cryptography::Decrypt($credential->password, $credential->hash) : null;
			}
		}
		else
		{
			$options['client_id'] = null;
			$options['client_secret'] = null;
		}

		if ($provider->proxy_server_id != null)
		{
			$proxyServer = (new ProxyServerAdapterFactory())->getInstance()->Read($provider->proxy_server_id);
			if (!$proxyServer->isNull())
			{
				$this->proxyOptions['hostname'] = (new ServerAdapterFactory())->getInstance()->Read($proxyServer->server_id)->address;
				$this->proxyOptions['port'] = $proxyServer->port;

				$proxyServerCredential = (new CredentialAdapterFactory())->getInstance()->Read($provider->credential_id);
				if (!$proxyServerCredential->isNull())
				{
					$this->proxyOptions['username'] = $proxyServerCredential->username;
					$this->proxyOptions['password']  = Cryptography::Decrypt($proxyServerCredential->password, $proxyServerCredential->hash);
				}
			}
		}

		$openIDNetworkInterface = new OpenIDNetworkInterface($this->baseURL, $this->proxyOptions);
		$metaData = $openIDNetworkInterface->GetMetaData(null);

		$options['authorization_endpoint'] = $metaData['authorization_endpoint'];
		$options['redirect_uri'] = $this->GetRedirectURI();
		$options['token_endpoint'] = $metaData['token_endpoint'];
		$options['userinfo_endpoint'] = $metaData['userinfo_endpoint'];
		$options['end_session_endpoint'] = $metaData['end_session_endpoint'];

		$this->options = $options;

		$this->scopes = ['email'];
	}

	public function GetAuthenticationURL(): string
	{
		$authParams = $this->GenerateAuthenticationParameters();
		return $this->AttachQuery($this->options['authorization_endpoint'], $this->GenerateQueryString($authParams));
	}

	public function GetLogoutURL(): string
	{
		$authParams = $this->GenerateAuthenticationParameters();
		return $this->AttachQuery($this->options['end_session_endpoint'], $this->GenerateQueryString($authParams));
	}

	public function GetState(): ?string
	{
		return $this->state;
	}

	public function GetAccessToken(array $input): ?string
	{
		$accessTokenURL = $this->options['token_endpoint'];

		$openIDNetworkInterface = new OpenIDNetworkInterface($accessTokenURL, $this->proxyOptions);

		$params = [
			'grant_type' => 'authorization_code',
			'client_id'     => $this->options['client_id'],
            'client_secret' => $this->options['client_secret'],
            'redirect_uri'  => $this->options['redirect_uri'],
		];

		$params = array_merge($params, $input);

		$encodedCredentials = base64_encode(sprintf('%s:%s', $params['client_id'], $params['client_secret']));
		$params['credential'] = $encodedCredentials;
		unset($params['client_id'], $params['client_secret']);

		$response = $openIDNetworkInterface->GetToken($this->GenerateQueryString($params), $params['credential']);

		if (is_array($response))
		{
			if (array_key_exists('error', $response))
			{
				$this->lastError = 'There was an error while trying to get an access token: ' . $response['error_description'];
			}
			else if (array_key_exists('access_token', $response))
			{
				if (array_key_exists('refresh_token', $response))
				{
					$this->session->set(self::SSO_REFRESH_TOKEN_SESSION, $response['refresh_token']);
				}
				return $response['access_token'];
			}
		}
		return null;
	}

	public function GetRefreshToken(array $input): ?string
	{
		$accessTokenURL = $this->options['token_endpoint'];

		$openIDNetworkInterface = new OpenIDNetworkInterface($accessTokenURL, $this->proxyOptions);

		$params = [
			'grant_type' => 'refresh_token',
			'client_id'     => $this->options['client_id'],
			'client_secret' => $this->options['client_secret'],
			'redirect_uri'  => $this->options['redirect_uri'],
		];

		$params = array_merge($params, $input);

		$encodedCredentials = base64_encode(sprintf('%s:%s', $params['client_id'], $params['client_secret']));
		$params['credential'] = $encodedCredentials;
		unset($params['client_id'], $params['client_secret']);

		$response = $openIDNetworkInterface->GetToken($this->GenerateQueryString($params), $params['credential']);

		if (is_array($response))
		{
			if (array_key_exists('error', $response))
			{
				$this->lastError = 'There was an error while trying to get a refresh token: ' . $response['error_description'];
			}
			else if (array_key_exists('access_token', $response))
			{
				return $response['access_token'];
			}
		}
		return null;
	}

	public function GetResourceOwner(string $token)
	{
		$resourceOwnerURL = $this->options['userinfo_endpoint'];

		$openIDNetworkInterface = new OpenIDNetworkInterface($resourceOwnerURL, $this->proxyOptions);

		return $openIDNetworkInterface->GetResourceOwner($token);
	}

	public function LinkUserToAccount(string $email, int $policy, string $ip_address, ?string $first_name = null, ?string $last_name = null): int
	{
		$id = $this->userAdapter->ReadIdByEmail($email);

		if (is_null($id))
		{
			if ($policy == SINGLE_SIGNON_POST_AUTH_CREATE_ACCOUNT)
			{
				// Create account
				return	$this->userAdapter->Create(
						(new UserFactory())->getInstanceFromParameters(
							$email, $email, $first_name, $last_name, $ip_address, null
					));
			}
			else if ($policy == SINGLE_SIGNON_POST_AUTH_LINK_ACCOUNT)
			{
				return -1;
			}
		}

		return $id;
	}

	protected function GenerateAuthenticationParameters(): array
	{
		$params = [
			'response_type'   => 'code',
			'approval_prompt' => 'auto'
		];

		if ($this->state == '')
		{
			$this->state = $this->GetRandomString();
		}

		$params['state'] = $this->state;

		if (count($this->scopes) == 0)
		{
			$this->scopes = $this->GetDefaultScopes();
		}
		$params['scopes'] = implode(' ', $this->scopes);

		$params['redirect_uri'] = $this->GetRedirectURI();
		$params['client_id'] = $this->options['client_id'];

		return $params;
	}

	protected function GetRandomString(int $length = 32): string
	{
		return bin2hex(random_bytes($length / 2));
	}

	protected function GetDefaultScopes(): array
	{
		return ['profile', 'email'];
	}

	protected function GetRedirectURI(): string
	{
		return base_url('Auth/Login');
	}

	protected function AttachQuery(string $url, string $query): string
	{
		$query = trim($query, '?&');

		if ($query)
		{
			$glue = strstr($url, '?') === false ? '?' : '&';
			return $url . $glue . $query;
		}

		return $url;
	}

	protected function GenerateQueryString(array $options): string
	{
		return http_build_query($options, '', '&', \PHP_QUERY_RFC3986);
	}

	public function GetPostAuthenticationPolicy(): int
	{
		return $this->provider->authentication_policy;
	}

	public function UpdateLastLogin(int $user_id): bool
	{
		return $this->userAdapter->UpdateLastLogin($user_id);
	}

	public function GetUserById(int $user_id): IEntity
	{
		return $this->userAdapter->Read($user_id);
	}

	public function GetLastError(): ?string
	{
		return $this->lastError;
	}

	public function GetProfileEndpoint()
	{
		return rtrim($this->baseURL, '/') . '/account/';
	}

    /**
	 * Get user id
	 * @return integer|null The user's ID from the session user data or NULL if not found
	 *
	 **/
    public function GetUserId(): int
	{
		if($this->session->has('user_id'))
		{
			return intval($this->session->get('user_id'));
		}

		return -1;
    }

	public function GetUserIdByToken(string $token): int
	{
		$resourceOwner = $this->GetResourceOwner($token);
		if (is_array($resourceOwner))
		{
			if (array_key_exists('email', $resourceOwner))
			{
				return $this->userAdapter->ReadIdByEmail($resourceOwner['email']);
			}
		}

		return -1;
	}

    public function RecordSession(\App\Libraries\CafeVariome\Entities\User $user)
	{
		$session_data = array(
			'user_id'                   => $user->getID(),
			'username'                  => $user->username,
			'email'                     => $user->email,
			'first_name'                => $user->first_name,
			'is_admin'                  => $user->is_admin,
		);
		$this->session->set($session_data);
    }

    public function LoggedIn(): bool
	{
        if ($this->session->has(self::SSO_RANDOM_STATE_SESSION))
		{
				$token = $this->GetRefreshToken(['refresh_token' => $this->session->get(self::SSO_REFRESH_TOKEN_SESSION)]);
				if (!is_null($token))
				{
					$this->session->set(self::SSO_RANDOM_STATE_SESSION, $token);
					return true;
				}
		}

        return false;
    }

	public function RemoveSession()
	{
		$this->session->remove(self::SSO_RANDOM_STATE_SESSION);
		$this->session->remove(self::SSO_REFRESH_TOKEN_SESSION);
		$this->session->remove(self::SSO_RANDOM_STATE_SESSION);
	}

    /**
	 * Check to see if the currently logged-in user is an admin.
	 * @return boolean Whether the user is an administrator
	 *
	 */
	public function IsAdmin(): bool
	{
        return $this->session->get('is_admin');
    }

    /**
     * Ping
     * Checks the availability of auth server.
     * @param N/A
     * @return bool
     */
    public function Ping(): bool
	{
        $curlOptions = [CURLOPT_NOBODY => true];
        $cURLAdapter = new cURLAdapter($this->baseURL, $curlOptions);

		$cURLAdapter->setOption(CURLOPT_FOLLOWLOCATION, true);
		$cURLAdapter->setOption(CURLOPT_HTTPPROXYTUNNEL, 1);
		if (count($this->proxyOptions) > 0)
		{
			$cURLAdapter->setOption(CURLOPT_PROXY, $this->proxyOptions['hostname']);
			$cURLAdapter->setOption(CURLOPT_PROXYPORT, $this->proxyOptions['port']);

			if ($this->proxyOptions['username'] != '' && $this->proxyOptions['password'] != '')
			{
				$cURLAdapter->setOption(CURLOPT_PROXYUSERPWD, $this->proxyOptions['username'] . ':' . $this->proxyOptions['password']);
			}
		}

        $cURLAdapter->Send();
        $httpStatus = $cURLAdapter->getInfo(CURLINFO_HTTP_CODE);

        return $httpStatus == 200;
    }
}
