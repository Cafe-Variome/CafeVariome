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

		while (true)
		{
			$this->socket->Accept();
			$request = $this->socket->Read(5000);
			try
			{
				$incomingMessage = json_decode($request);

				if (json_last_error() == JSON_ERROR_NONE)
				{
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
									'batch' => $incomingMessage->batch,
									'data_file_id' => $task->data_file_id,
									'source_id' => $task->source_id
								];
							}
							break;
						case 'reportprogressmessage':
							$task_id = $incomingMessage->task_id;
							if(
								array_key_exists($installation_key, $tasks) &&
								array_key_exists($task_id, $tasks[$installation_key])
							)
							{
								$currentTask = $tasks[$installation_key][$task_id];
								$currentTask['progress'] = $incomingMessage->progress;
								$currentTask['finished'] = $incomingMessage->finished;
								$currentTask['status'] = $incomingMessage->status;

								$tasks[$installation_key][$task_id] = $currentTask;
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
									$resp[$taskId]['source_id'] = $taskInfo['source_id'];
									$resp[$taskId]['batch'] = $taskInfo['batch'];

									if($taskInfo['finished'])
									{
										unset($tasks[$installation_key][$taskId]);
									}
								}
							}

							$this->socket->Send($resp);
							break;
						case 'shutdownmessage':
							$this->Shutdown();
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
