<?php namespace App\Libraries\CafeVariome\Auth;

/**
 * Name: LocalAuthenticator.php
 * Created: 31/05/2020
 * @author Mehdi Mehtarizadeh
 *
 */

use App\Libraries\CafeVariome\Database\UserAdapter;
use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\User;
use App\Libraries\CafeVariome\Factory\UserAdapterFactory;
use CodeIgniter\Session\Session;
use Config\Services;

class LocalAuthenticator implements  IAuthenticator
{
	protected Session $session;

	protected UserAdapter $userAdapter;

	public function __construct()
	{
		$this->session =  Services::session();
		$this->userAdapter = (new UserAdapterFactory())->GetInstance();
	}

	/**
	 * @inheritDoc
	 */
	public function GetUserIdByEmail(string $email): ?int
	{
		return $this->userAdapter->ReadIdByEmail($email);
	}

	/**
	 * @inheritDoc
	 */
	public function GetUserById(int $user_id): IEntity
	{
		return $this->userAdapter->Read($user_id);
	}

	/**
	 * @inheritDoc
	 */
	public function GetUserId(): int
	{
		return $this->session->has('user_id') ? $this->session->get('user_id') : -1;
	}

	/**
	 * @inheritDoc
	 */
	public function LoggedIn(): bool
	{
		return $this->session->has('user_id');
	}

	/**
	 * @inheritDoc
	 */
	public function IsAdmin(): bool
	{
		return $this->session->has('is_admin') && $this->session->get('is_admin');
	}

	/**
	 * @inheritDoc
	 */
	public function RecordSession(User $user): void
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

	/**
	 * @inheritDoc
	 */
	public function GetLogoutURL(): string
	{
		return base_url('Home/Index');
	}

	/**
	 * @inheritDoc
	 */
	public function GetProfileEndpoint(): string
	{
		return base_url('Auth/Profile');
	}

	/**
	 * @return bool
	 * As this is a local authenticator it always returns true.
	 */
	public function Ping(): bool
	{
		return true;
	}
}
