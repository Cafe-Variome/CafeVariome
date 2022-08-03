<?php namespace App\Libraries\CafeVariome\Net;

/**
 * ServiceInterface.php
 *
 * Created: 17/02/2021
 *
 * @author Mehdi Mehtarizadeh
 *
 * This class interfaces with Cafe Variome Service process.
 *
 */

use App\Libraries\CafeVariome\Factory\PollProgressMessageFactory;
use App\Libraries\CafeVariome\Factory\RegisterTaskMessageFactory;
use App\Libraries\CafeVariome\Factory\ReportProgressMessageFactory;
use App\Libraries\CafeVariome\Helpers\Shell\PHPShellHelper;

class ServiceInterface
{
    private $socket;
    private $config;

    public function __construct()
	{
        $this->config = config('BackgroundService');
        $this->socket = new SocketAdapter($this->config->address, $this->config->port);
    }

    public function ping(): bool
    {
        return $this->socket->Create()->Connect()->isConnected();
    }

    public function Start()
    {
		PHPShellHelper::runAsync(getcwd() . "/index.php Task StartService");
    }

	public function PollTasks()
	{
		$message = (new PollProgressMessageFactory())->GetInstance();

		$results = "";
		$this->socket->Create()->Connect()->Write($message);
		while ($out = $this->socket->Read(2048)) {
			$results .= $out;
		}
		$this->socket->Close();

		return $results;
	}

    public function GetUploadedFilesStatus()
    {
        $message = ['type' => 'uploadedfilesstatus'];

        $results = "";
        $this->socket->Create()->Connect()->Write($message);
        while ($out = $this->socket->Read(2048)) {
            $results .= $out;
        }
        $this->socket->Close();

        return $results;
    }

    public function GetElasticsearchStatus()
    {
        $message = ['type' => 'elasticsearchstatus'];

        $results = "";
        $this->socket->Create()->Connect()->Write($message);
        while ($out = $this->socket->Read(2048)) {
            $results .= $out;
        }
        $this->socket->Close();

        return $results;
    }

	public function GetNeo4JStatus()
	{
		$message = ['type' => 'neo4jstatus'];

		$results = "";
		$this->socket->Create()->Connect()->Write($message);
		while ($out = $this->socket->Read(2048)) {
			$results .= $out;
		}
		$this->socket->Close();

		return $results;
	}

	public function GetUserInterfaceIndexStatus()
	{
		$message = ['type' => 'uiindexstatus'];

		$results = "";
		$this->socket->Create()->Connect()->Write($message);
		while ($out = $this->socket->Read(2048)) {
			$results .= $out;
		}
		$this->socket->Close();

		return $results;
	}

    public function RegisterTask(int $task_id, string $status = ''): bool
    {
		$message = (new RegisterTaskMessageFactory())->GetInstance($task_id, $status);

        try
		{
            $this->socket->Create()->Connect()->Write($message, 10);
            $this->socket->Close();

            return true;
        }
		catch (\Exception $ex)
		{
            error_log($ex->getMessage());
        }

        return false;
    }

    public function ReportProgress(int $entity_id, int $records_processed, int $total_records, string $name, string $status = "", bool $finished = false, string $message = "records_processed"): bool
    {
        $message = [
            'type' => 'reportprogress',
            'process' => [
                'pid' => getmypid(),
                'name' => $name, //'bulkupload'
                'entityId' => $entity_id,
                'message' => $message,
                'count' => $records_processed,
                'total'  => $total_records,
                'status' => $status,
                'finished' => $finished
            ]
        ];

        try {
            $this->socket->Create()->Connect()->Write($message, 10);
            $this->socket->Close();

            return true;
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
        }

        return false;
    }
}
