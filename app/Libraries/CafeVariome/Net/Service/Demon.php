<?php namespace App\Libraries\CafeVariome\Net\Service;

use App\Libraries\CafeVariome\Net\SocketAdapter;

/**
 * Demon.php
 * Created: 15/02/2022
 * @author Mehdi Mehtarizadeh
 *
 */

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
				$requestArr = json_decode($request, true);
				if (json_last_error() == JSON_ERROR_NONE)
				{
					$process = [];
					$type = $requestArr['type'];
					$installation_key = $requestArr['installation_key'];

					switch (strtolower($type))
					{
						case 'registerprocess':
							$process = $requestArr['process'];
							$pid = $process['pid'];
							$name = $process['name'];
							$entityId = $process['entityId'];
							$message = $process['message'];
							$status = $process['status'];
							$finished = $process['finished'];
							if(
								!array_key_exists($entityId, $tasks) &&
								strtolower($message) === 'records_count'
							)
							{
								$records_count = $process['count'];
								$tasks[$installation_key][$entityId] = [
									'pid' => $pid,
									'installation_key' => $installation_key,
									'message' => $message,
									'name' => $name,
									'entityId' => $entityId,
									'records_count' => $records_count,
									'finished' => $finished,
									'status' => $status
								];
							}
							break;
						case 'reportprogress':
							$process = $requestArr['process'];
							$pid = $process['pid'];
							$name = $process['name'];
							$entityId = $process['entityId'];
							$message = $process['message'];
							$status = $process['status'];
							$finished = $process['finished'];
							if(
								array_key_exists($installation_key, $tasks) &&
								array_key_exists($entityId, $tasks[$installation_key]) &&
								strtolower($message) === 'records_processed')
							{
								$records_processed = $process['count'];
								$total_records = $process['total'];

								$task = $tasks[$installation_key][$entityId];
								$task['records_processed'] = $records_processed;
								$task['records_count'] = $total_records;
								$task['finished'] = $finished;

								if ($status != "") {
									$task['status'] = $status;
								}

								$tasks[$installation_key][$entityId] = $task;
							}
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
}
