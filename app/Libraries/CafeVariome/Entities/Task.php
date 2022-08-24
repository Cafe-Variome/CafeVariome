<?php namespace App\Libraries\CafeVariome\Entities;

/**
 * Task.php
 * Created 10/06/2022
 *
 * This class extends Entity and implements IEntity.
 * @author Mehdi Mehtarizadeh
 */

class Task extends Entity
{
	/**
	 * @var int|null id of data file the task is processing
	 */
	public ?int $data_file_id;

	/**
	 * @var int task type
	 */
	public int $type;

	/**
	 * @var int|null Unix timestamp of start date and time
	 */
	public ?int $started;

	/**
	 * @var int|null
	 */
	public ?int $ended;

	/**
	 * @var int id of the user who started the job
	 */
	public int $user_id;

	/**
	 * @var int|null id of the pipeline used to process the job
	 */
	public ?int $pipeline_id;

	/**
	 * @var int|null id of the source the task is associated with
	 */
	public ?int $source_id;

	/**
	 * @var int progress in percentage
	 */
	public int $progress;

	/**
	 * @var int error code or 0 for success
	 */
	public int $error_code;

	/**
	 * @var bool whether to overwrite data that the task is going to process or not
	 */
	public bool $overwrite;

	/**
	 * @var string|null error message text
	 */
	public ?string $error_message;

	/**
	 * @var int status of the job
	 */
	public int $status;

	public function SetError(int $error_code, string $complementary_error_message = ''): static
	{
		if ($this->error_code)
		{
			$this->error_code = $error_code;
		}
		else
		{
			$this->error_code += $error_code;

		}
		if ($error_code > 0)
		{
			$this->error_message = $this->GetErrorMessage() . ' ' . $complementary_error_message;
		}

		return $this;
	}

	public function GetErrorMessage(): ?string
	{
		switch ($this->error_code)
		{
			case TASK_ERROR_NO_ERROR:
				return null;
			case TASK_ERROR_RUNTIME_ERROR:
				return 'An error occurred while processing the task: ';
			case TASK_ERROR_DATA_FILE_ID_NULL:
				return 'Data File Id is null.';
			case TASK_ERROR_PIPELINE_ID_NULL:
				return 'Pipeline Id is null.';
			case TASK_ERROR_SOURCE_ID_NULL:
				return 'Source Id is null.';
			case TASK_ERROR_DATA_FILE_NULL:
				return 'Data File record does not exist.';
			case TASK_ERROR_PIPELINE_NULL:
				return 'Pipeline does not exist.';
			case TASK_ERROR_DUPLICATE:
				return 'Another task is running.';
		}

		return 'Undefined error.';
	}

}
