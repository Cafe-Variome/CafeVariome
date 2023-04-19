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
use App\Libraries\CafeVariome\Factory\ShutdownMessageFactory;
use App\Libraries\CafeVariome\Helpers\Shell\PHPShellHelper;

class ServiceInterface
{
	/**
	 * @var SocketAdapter socket adapter object used to communicate with the demon
	 */
    private SocketAdapter $socket;

	/**
	 * @var object|null config object retrieved from CI to initiate socket
	 */
    private object $config;

	/**
	 * @var string installation key of instance transferred to message object
	 */
	private string $installation_key;

    public function __construct(string $installation_key)
	{
        $this->config = config('BackgroundService');
        $this->socket =	new SocketAdapter($this->config->address, $this->config->port);
		$this->installation_key = $installation_key;
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
		$message = (new PollProgressMessageFactory())->GetInstance()->SetInstallationKey($this->installation_key);

		$results = "";
		$this->socket->Create()->Connect()->Write($message);
		while ($out = $this->socket->Read(2048)) {
			$results .= $out;
		}
		$this->socket->Close();

		return $results;
	}

	public function Shutdown()
	{
		$message = (new ShutdownMessageFactory())->GetInstance()->SetInstallationKey($this->installation_key);

		$results = "";
		$this->socket->Create()->Connect()->Write($message);
		while ($out = $this->socket->Read(2048))
		{
			$results .= $out;
		}
		$this->socket->Close();

		return $results;
	}

    public function RegisterTask(int $task_id, bool $batch = false, string $status = ''): bool
    {
		$message = (new RegisterTaskMessageFactory())->GetInstance($task_id, $status, $batch)->SetInstallationKey($this->installation_key);

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

    public function ReportProgress(int $task_id, int $progress, string $status = '', bool $finished = false): array
    {
        $message = (new ReportProgressMessageFactory())->GetInstance($task_id, $progress, $finished, $status)->SetInstallationKey($this->installation_key);

        try
		{
            $this->socket->Create()->Connect()->Write($message, 10);
			$results = '';
			while ($out = $this->socket->Read(2048))
			{
				$results .= $out;
			}
            $this->socket->Close();

			$responseArray = json_decode($results, true);

			return 	[
				'response_received' => true,
				'error' => null,
				'payload' => $responseArray
			];
        }
		catch (\Exception $ex)
		{
			return [
				'response_received' => false,
				'error' => $ex->getMessage(),
				'payload' => null
			];
        }
    }
}
