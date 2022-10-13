<?php namespace App\Libraries\CafeVariome\Entities\ViewModels;

use App\Libraries\CafeVariome\Helpers\UI\DataFileHelper;

/**
 * DataFileList.php
 * Created 10/08/2022
 *
 * @author Mehdi Mehtarizadeh
 */

class DataFileList extends BaseViewModel
{
	public string $name;

	public string $size;

	public string $upload_date;

	public int $record_count;

	public string $status;

	public string $status_text;

	public int $user_id;

	public string $user_username;

	public string $user_first_name;

	public string $user_last_name;

	public function __construct(object $input = null)
	{
		if (!is_null($input))
		{
			parent::__construct($input);
			$this->status_text = DataFileHelper::GetDataFileStatus($this->status);
		}
	}

}
