<?php namespace App\Libraries\CafeVariome\Net;

/**
 * ServiceInterface.php
 * 
 * Created: 17/02/2021
 * 
 * @author Mehdi Mehtarizadeh
 * 
 * This is class that interfaces with Cafe Variome Service process.
 * 
 */

class ServiceInterface 
{
    private $socket;
    private $config;

    public function __construct() {
        $this->config = config('BackgroundService');
        $this->socket = new SocketAdapter($this->config->address, $this->config->port);
    }

    public function ping()
    {
        return $this->socket->Create()->Connect()->isConnected();
    }

    public function Start()
    {
        $cmd = PHP_BIN_PATH . " " . $this->config->binPath . ' >/dev/null 2>&1 &';
        $r = shell_exec($cmd);
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

    public function RegisterProcess(int $entity_id, int $total_records, string $name): bool
    {
        $message = [
            'type' => 'registerprocess',
            'process' => [
                'pid' => getmypid(), 
                'name' => $name,
                'entityId' => $entity_id,
                'message' => 'records_count',
                'count' => $total_records
            ]
        ];

        try {
            $this->socket->Create()->Connect()->Write($message, 100);
            $this->socket->Close();

            return true;
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
        }

        return false;
    }

    public function ReportProgress(int $entity_id, int $records_processed, int $total_records, string $name): bool
    {
        $message = [
            'type' => 'reportprogress',
            'process' => [
                'pid' => getmypid(), 
                'name' => $name, //'bulkupload'
                'entityId' => $entity_id,
                'message' => 'records_processed',
                'count' => $records_processed,
                'total'  => $total_records
            ]
        ];

        try {
            $this->socket->Create()->Connect()->Write($message, 100);
            $this->socket->Close();

            return true;
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
        }

        return false;
    }
}
