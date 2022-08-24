<?php namespace App\Libraries\CafeVariome\Net\Service;

/**
 * Demon.php
 * Created: 15/02/2022
 * @author Mehdi Mehtarizadeh
 *
 */

use App\Libraries\CafeVariome\Factory\TaskAdapterFactory;
use App\Libraries\CafeVariome\Net\SocketAdapter;


class Demon
{
	private $socket;
	private $config;

	public function __construct()
	{
		$this->config = config('BackgroundService');
		$this->socket = new SocketAdapter($this->config->address, $this->config->port);
	}

	public function Run()
	{
		$taskAdapter = (new TaskAdapterFactory())->GetInstance();

		$this->socket->Create(); // Create a socket
		$this->socket->setOption(SOL_SOCKET, SO_REUSEADDR, 1); // Set relevant options
		$this->socket->Bind(); // Bind it to the address and port specified in BackgroundService config file

		//Listen to incoming requests
		$this->socket->Listen();

		$tasks = [];
		$records = -1;
		$processedRecords = 0;
		while (true)
		{
			$this->socket->Accept();
			$request = $this->socket->Read(5000);
			try
			{
				$incomingMessage = json_decode($request);
				if (json_last_error() == JSON_ERROR_NONE)
				{
					$process = [];
					$type = $incomingMessage->type;
					$installation_key = $incomingMessage->installation_key;

					switch (strtolower($type))
					{
						case 'registertaskmessage':
							$task_id = $incomingMessage->task_id;
							$task = $taskAdapter->Read($task_id);

							if (!$task->isNull())
							{
								$tasks[$installation_key][$task_id] = [
									'error' => 0,
									'continue' => true,
									'progress' => 0,
									'finished' => $incomingMessage->finished,
									'status' => $incomingMessage->status,
									'process_id' => $incomingMessage->process_id,
									'data_file_id' => $task->data_file_id
								];
							}

							break;
						case 'reportprogressmessage':
							if(
								array_key_exists($installation_key, $tasks) &&
								array_key_exists($task_id, $tasks[$installation_key])
							)
							{
								$task = $tasks[$installation_key][$task_id];
								$task['progress'] = $incomingMessage->progress;
								$task['finished'] = $incomingMessage->finished;
								$task['status'] = $incomingMessage->status;

								$tasks[$installation_key][$task_id] = $task;

//								$resp[$task_id]['progress'] = $tasks[$installation_key][$task_id]['progress'];
//								$resp[$task_id]['status'] = $tasks[$installation_key][$task_id]['status'];
//								$resp[$task_id]['finished'] = $tasks[$installation_key][$task_id]['finished'];
								$resp[$task_id]['continue'] = $tasks[$installation_key][$task_id]['continue'];

								$this->socket->Send($resp);
							}
							break;
						case 'pollprogressmessage':
							$resp = [];

							if (array_key_exists($installation_key, $tasks))
							{
								foreach ($tasks[$installation_key] as $taskId => $taskInfo)
								{
									$resp[$taskId]['progress'] = $taskInfo['progress'];
									$resp[$taskId]['finished'] = $taskInfo['finished'];
									$resp[$taskId]['status'] = $taskInfo['status'];
									$resp[$taskId]['data_file_id'] = $taskInfo['data_file_id'];

									if($taskInfo['finished'])
									{
										unset($tasks[$installation_key][$taskId]);
									}
								}
							}

							$this->socket->Send($resp);
							break;
						case 'uploadedfilesstatus':
							$resp = [];

							if (array_key_exists($installation_key, $tasks))
							{
								foreach ($tasks[$installation_key] as $taskId => $taskInfo)
								{
									if (array_key_exists('records_count', $taskInfo) && $taskInfo['name'] == 'bulkupload')
									{
										$rc = $taskInfo['records_count'];
										if (array_key_exists('records_processed', $taskInfo))
										{
											$rp = $taskInfo['records_processed'];
										}

										$progress = ceil((($rp / $rc)) * 100.0);

										$resp[$taskId]['progress'] = $progress;
										$resp[$taskId]['status'] = $taskInfo['status'];
										$resp[$taskId]['finished'] = $taskInfo['finished'];

										if($taskInfo['finished'])
										{
											unset($tasks[$installation_key][$taskId]);
										}
									}
								}
							}
							$this->socket->Send($resp);

							break;
						case 'elasticsearchstatus':
							$resp = [];

							if (array_key_exists($installation_key, $tasks)) {

								foreach ($tasks[$installation_key] as $taskId => $taskInfo) {
									if (array_key_exists('records_count', $taskInfo) && $taskInfo['name'] == 'elasticsearchindex') {
										$rc = $taskInfo['records_count'];

										$rp = 0;
										if (array_key_exists('records_processed', $taskInfo)) {
											$rp = $taskInfo['records_processed'];
										}

										$progress = ceil((($rp / $rc)) * 100.0);

										$resp[$taskId]['progress'] = $progress;
										$resp[$taskId]['status'] = $taskInfo['status'];
										$resp[$taskId]['finished'] = $taskInfo['finished'];

										if($taskInfo['finished']) {
											unset($tasks[$installation_key][$taskId]);
										}
									}
								}
							}

							$this->socket->Send($resp);
							break;
						case 'neo4jstatus':
							$resp = [];

							if (array_key_exists($installation_key, $tasks)) {

								foreach ($tasks[$installation_key] as $taskId => $taskInfo) {
									if (array_key_exists('records_count', $taskInfo) && $taskInfo['name'] == 'neo4jindex') {
										$rc = $taskInfo['records_count'];

										$rp = 0;
										if (array_key_exists('records_processed', $taskInfo)) {
											$rp = $taskInfo['records_processed'];
										}

										$progress = ceil((($rp / $rc)) * 100.0);

										$resp[$taskId]['progress'] = $progress;
										$resp[$taskId]['status'] = $taskInfo['status'];
										$resp[$taskId]['finished'] = $taskInfo['finished'];

										if($taskInfo['finished']) {
											unset($tasks[$installation_key][$taskId]);
										}
									}
								}
							}

							$this->socket->Send($resp);
							break;
						case 'uiindexstatus':
							$resp = [];

							if (array_key_exists($installation_key, $tasks)) {

								foreach ($tasks[$installation_key] as $taskId => $taskInfo) {
									if (array_key_exists('records_count', $taskInfo) && $taskInfo['name'] == 'uiindex') {
										$rc = $taskInfo['records_count'];

										$rp = 0;
										if (array_key_exists('records_processed', $taskInfo)) {
											$rp = $taskInfo['records_processed'];
										}

										$progress = ceil((($rp / $rc)) * 100.0);

										$resp[$taskId]['progress'] = $progress;
										$resp[$taskId]['status'] = $taskInfo['status'];
										$resp[$taskId]['finished'] = $taskInfo['finished'];

										if($taskInfo['finished']) {
											unset($tasks[$installation_key][$taskId]);
										}
									}
								}
							}

							$this->socket->Send($resp);
							break;
					}
				}
				$this->socket->Close();
			}
			catch(Exception $ex)
			{
				var_dump($ex->getMessage());
				$this->socket->Close();
			}
		}
	}

	public function Shutdown()
	{
		error_log('Shutting down daemon.');
		exit();
	}
}
