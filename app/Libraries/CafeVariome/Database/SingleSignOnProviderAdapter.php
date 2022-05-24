<?php namespace App\Libraries\CafeVariome\Database;

/**
 * SingleSignOnProviderAdapter.php
 * Created 22/05/2022
 *
 * This class offers CRUD operation for SingleSignOnProvider.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\SingleSignOnProviderFactory;

class SingleSignOnProviderAdapter extends BaseAdapter
{
	/**
	 * @inheritDoc
	 */
	protected string $table = 'single_sign_on_providers';

	/**
	 * @inheritDoc
	 */
	protected string $key = 'id';

	/**
	 * Converts general PHP objects to a SingleSignOnProvider object.
	 * @param object|null $object
	 * @return IEntity
	 * @throws \Exception
	 */
    public function toEntity(?object $object): IEntity
    {
		$singleSignOnProviderFactory = new SingleSignOnProviderFactory();
		return $singleSignOnProviderFactory->getInstance($object);
    }
}
