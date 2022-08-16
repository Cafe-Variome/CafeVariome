<?php namespace App\Libraries\CafeVariome\Database;

/**
 * CredentialAdapter.php
 * Created 22/04/2022
 *
 * This class offers CRUD operation for Credential.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\CredentialFactory;

class CredentialAdapter extends BaseAdapter
{
	/**
	 * @inheritDoc
	 */
	protected static string $table = 'credentials';

	/**
	 * @inheritDoc
	 */
	protected static string $key = 'id';

	/**
	 * Converts general PHP objects to a Credential object.
	 * @param object|null $object
	 * @return IEntity
	 * @throws \Exception
	 */
    public function toEntity(?object $object): IEntity
    {
        $credentialFactory = new CredentialFactory();
		return $credentialFactory->GetInstance($object);
    }
}
