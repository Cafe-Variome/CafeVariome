<?php namespace App\Libraries\CafeVariome\Auth;

use App\Libraries\CafeVariome\Database\UserAdapter;
use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\User;
use App\Libraries\CafeVariome\Factory\UserAdapterFactory;

/**
 * Name: LocalAuthenticator.php
 * Created: 31/05/2020
 * @author Mehdi Mehtarizadeh
 *
 */

class LocalAuthenticator
{
	protected $session;

	protected UserAdapter $userAdapter;

	public function __construct()
	{
		$this->session =  \Config\Services::session();
		$this->userAdapter = (new UserAdapterFactory())->getInstance();
	}

	public function GetUserIdByEmail(string $email): ?int
	{
		return $this->userAdapter->ReadIdByEmail($email);
	}

	public function GetUserById(int $user_id): IEntity
	{
		return $this->userAdapter->Read($user_id);
	}

	public function GetUserId(): int
	{
		return $this->session->has('user_id') ? $this->session->get('user_id') : -1;
	}

	public function LoggedIn(): bool
	{
		return $this->session->has('user_id');
	}

	public function IsAdmin(): bool
	{
		return $this->session->has('is_admin') && $this->session->get('is_admin');
	}

	public function RecordSession(User $user)
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

	public function GetLogoutURL(): string
	{
		return base_url('Home/Index');
	}

	public function GetProfileEndpoint(): string
	{
		return base_url('Auth/Profile');
	}

	public function Ping(): bool
	{
		return true;
	}
}
