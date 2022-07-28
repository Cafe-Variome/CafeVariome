<?php namespace App\Libraries\CafeVariome\Entities;

/**
 * Server.php
 * Created 10/06/2022
 *
 * This class extends Entity and implements IEntity.
 * @author Mehdi Mehtarizadeh
 */

class DataFile extends Entity
{
	/**
	 * @var string file name as saved on user machine and displayed in the UI
	 */
	public string $name;

	/**
	 * @var string file name as saved on the server
	 */
	public string $disk_name;

	/**
	 * @var int file size in bytes
	 */
	public float $size;

	/**
	 * @var int Unix timestamp of upload date and time
	 */
	public int $upload_date;

	/**
	 * @var int number of records in the file
	 */
	public int $record_count;

	/**
	 * @var int id of user who uploaded the file
	 */
	public int $user_id;

	/**
	 * @var int id of the source the file belongs to
	 */
	public int $source_id;

	/**
	 * @var int upload status
	 */
	public int $status;

	/**
	 * @var User object of $user_id
	 */
	public User $user;

}
