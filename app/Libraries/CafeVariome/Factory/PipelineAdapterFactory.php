<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * PipelineAdapterFactory.php
 * Created 30/05/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\IAdapter;
use App\Libraries\CafeVariome\Database\PipelineAdapter;

class PipelineAdapterFactory extends AdapterFactory
{

    /**
     * @inheritDoc
     */
    public function GetInstance(): IAdapter
    {
        return new PipelineAdapter();
    }
}
