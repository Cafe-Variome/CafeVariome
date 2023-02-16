<?php namespace APP\Libraries\CafeVariome\Auth;

/**
 * IAuthenticator.php
 *
 * @author: Mehdi Mehtarizadeh
 * Created: 14/02/2023
 *
 * This interface defines the behaviour of an Authenticator class.
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\User;

interface IAuthenticator
{
	/**
	 * @param string $email
	 * @return int|null
	 * Fetches user ID by email address or null if the ID doesn't exist.
	 */
	public function GetUserIdByEmail(string $email): ?int;

	/**
	 * @param int $user_id
	 * @return IEntity
	 * Fetches Entity object of the User ID or NullEntity if the user ID doesn't exist.
	 */
	public function GetUserById(int $user_id): IEntity;

	/**
	 * @return int
	 * Fetches authenticated user ID from sessions.
	 */
	public function GetUserId(): int;

	/**
	 * @return bool
	 * To check whether a user is logged in or not, checks sessions
	 */
	public function LoggedIn(): bool;

	/**
	 * @return bool
	 * To check whether a user is admin or not
	 */
	public function IsAdmin(): bool;

	/**
	 * @param User $user
	 * @return void
	 * Records session data after successful authentication.
	 */
	public function RecordSession(User $user): void;

	/**
	 * @return string
	 * Gets logout URL to redirect the user to.
	 */
	public function GetLogoutURL(): string;

	/**
	 * @return string
	 * Gets profile page URl to redirect the user to.
	 */
	public function GetProfileEndpoint(): string;

	/**
	 * @return bool
	 * Used to check whether external authenticators are available or not.
	 * For internal authenticators returns true always.
	 */
	public function Ping(): bool;
}
