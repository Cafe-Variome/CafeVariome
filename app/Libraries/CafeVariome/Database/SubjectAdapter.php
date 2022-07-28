<?php namespace App\Libraries\CafeVariome\Database;

/**
 * SubjectAdapter.php
 * Created 27/07/2022
 *
 * This class offers CRUD operation for Subject.
 * @author Mehdi Mehtarizadeh
 */


use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\SubjectFactory;

class SubjectAdapter extends BaseAdapter
{
	/**
	 * @inheritDoc
	 */
	protected string $table = 'subjects';

	/**
	 * @inheritDoc
	 */
	protected string $key = 'id';

	/**
	 * Converts general PHP objects to a Subject object.
	 * @param object|null $object
	 * @return IEntity
	 * @throws \Exception
	 */
    public function toEntity(?object $object): IEntity
    {
        $subjectFactory = new SubjectFactory();
		return $subjectFactory->GetInstance($object);
    }
}
