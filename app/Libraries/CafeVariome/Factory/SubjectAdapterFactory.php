<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * SubjectAdapterFactory.php
 * Created 27/07/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\IAdapter;
use App\Libraries\CafeVariome\Database\SubjectAdapter;

class SubjectAdapterFactory extends AdapterFactory
{
    /**
     * @inheritDoc
     */
    public function GetInstance(): SubjectAdapter
    {
        return new SubjectAdapter();
    }
}
