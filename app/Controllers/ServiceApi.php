<?php namespace App\Controllers;

/**
 * ServiceApi.php
 *
 * Created : 17/02/2021
 *
 * @author Mehdi Mehtarizadeh
*/

use App\Libraries\CafeVariome\CafeVariome;
use App\Libraries\CafeVariome\Database\IAdapter;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Libraries\CafeVariome\Net\ServiceInterface;

class ServiceApi extends ResourceController
{
	/**
	 * @var IAdapter Setting adapter instance
	 */
	protected IAdapter $setting;

	/**
	 * @var ServiceInterface Service Interface object to interact with the demon
	 */
	protected ServiceInterface $serviceInterface;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
		$this->setting =  CafeVariome::Settings();
		$this->serviceInterface = new ServiceInterface($this->setting->GetInstallationKey());
    }

	public function PollTasks()
	{
		$result = "retry: 3000\nid: 0\ndata: {\"progress\": \"-1\", \"status\": \"\"}\n\n";
		try
		{
			$fileStatus = json_decode($this->serviceInterface->PollTasks(), true);

			if (json_last_error() == JSON_ERROR_NONE && count($fileStatus) > 0)
			{
				$result = "retry: 1000\n";
				foreach ($fileStatus as $taskId => $value)
				{
					$result .= "id: " . $taskId . "\n";
					$result .= "data: {\"progress\": \"" . $value['progress'] . "\", \"status\": \"" . $value['status'] . "\", \"data_file_id\": \"" . $value['data_file_id'] . "\", \"source_id\": \"" . $value['source_id'] . "\", \"batch\": \"" . $value['batch'] . "\"}\n\n";
				}
			}
		}
		catch (\Exception $ex)
		{

		}

		$this->response->setHeader("Content-Type", "text/event-stream");
		$this->response->setHeader("Cache-Control", "no-cache");


		return $this->respond($result);
	}

	public function PollTask()
	{
		if ($this->request->getMethod() == 'post')
		{
			$task_id = $this->request->getVar('task_id');

			$result = "retry: 3000\nid: 0\ndata: {\"progress\": \"-1\", \"status\": \"\"}\n\n";

			try
			{
				$fileStatus = json_decode($this->serviceInterface->PollTasks(), true);

				if (json_last_error() == JSON_ERROR_NONE && count($fileStatus) > 0)
				{
					$result = "retry: 1000\n";
					if (array_key_exists($task_id, $fileStatus))
					{
						$task = $fileStatus[$task_id];
						$result .= "id: " . $task_id . "\n";
						$result .= "data: {\"progress\": \"" . $task['progress'] . "\", \"status\": \"" . $task['status'] . "\", \"data_file_id\": \"" . $task['data_file_id'] . "\", \"source_id\": \"" . $task['source_id'] . "\"}\n\n";
					}
				}
			}
			catch (\Exception $ex)
			{

			}

			$this->response->setHeader("Content-Type", "text/event-stream");
			$this->response->setHeader("Cache-Control", "no-cache");


			return $this->respond($result);
		}
	}
}
