<?php namespace App\Libraries\CafeVariome\Database;

/**
 * PipelineAdapter.php
 * Created 30/05/2022
 *
 * This class offers CRUD operation for Pipeline.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\PipelineFactory;

class PipelineAdapter extends BaseAdapter
{
	/**
	 * @inheritDoc
	 */
	protected static string $table = 'pipelines';

	/**
	 * @inheritDoc
	 */
	protected static string $key = 'id';

    /**
     * @inheritDoc
     */
    public function toEntity(?object $object): IEntity
    {
        $pipelineFactory = new PipelineFactory();
		return $pipelineFactory->GetInstance($object);
    }
}
