<?php namespace App\Libraries\CafeVariome\Database;

/**
 * AttributeAdapter.php
 * Created 28/07/2022
 *
 * This class offers CRUD operation for Attribute.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\AttributeFactory;

class AttributeAdapter extends BaseAdapter
{

    /**
     * @inheritDoc
     */
    public function toEntity(?object $object): IEntity
    {
        $attributeFactory = new AttributeFactory();
		return $attributeFactory->GetInstance($object);
    }
}
