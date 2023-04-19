<?php namespace App\Libraries\CafeVariome\Auth;

/**
 * Name NullAuthenticator.php
 *
 * Created 18/04/2023
 * @author Mehdi Mehtarizadeh
 *
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\User;

class NullAuthenticator implements IAuthenticator
{

    /**
     * @inheritDoc
     */
    public function GetUserIdByEmail(string $email): ?int
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function GetUserById(int $user_id): IEntity
    {
        return new NullEntity();
    }

    /**
     * @inheritDoc
     */
    public function GetUserId(): ?int
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function LoggedIn(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function IsAdmin(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function RecordSession(User $user): void
    {

    }

    /**
     * @inheritDoc
     */
    public function GetLogoutURL(): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function GetProfileEndpoint(): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function Ping(): bool
    {
        return true;
    }
}
