<?php namespace App\Libraries\CafeVariome\Entities\ViewModels;

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

	public int $user_id;

	public string $user_username;

	public string $user_first_name;

	public string $user_last_name;

}
