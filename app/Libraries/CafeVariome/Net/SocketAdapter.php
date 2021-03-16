<?php namespace App\Libraries\CafeVariome\Net;

/**
 * SocketAdapter.php
 * Created: 16/02/2021
 * 
 * @author Mehdi Mehtarizadeh
 * 
 */

use App\Models\Settings;

class SocketAdapter extends NetworkAdapter
{
    private $address;
    private $port;
    private $socket;
    private $defaultConfig;
    private $connected;

    public function __construct(string $address, int $port) {
        $this->address = $address;
        $this->port = $port;
        $this->defaultConfig = ['domain' => AF_INET, 'type' => SOCK_STREAM, 'protocol' => SOL_TCP];
        $this->connected = false;
    }

    /**
     * @param array $config: domain, type, and protocol configuration 
     */
    public function Create(array $config = null)
    {
        if (!$config || count($config) == 0) {
            $config = $this->defaultConfig;
        }
        $this->socket = socket_create($config['domain'], $config['type'], $config['protocol']);
        return $this;
    }

    public function Connect()
    {
        $this->connected = @socket_connect($this->socket, $this->address, $this->port);
        return $this;
    }

    public function Read(int $length, int $delay = 0, int $type = PHP_BINARY_READ)
    {
        $input = socket_read($this->socket, $length, $type);

        if ($delay > 0) {
            $this->delay($delay);
        }

        return $input;
    }


    public function Write(array $message, int $delay = 0)
    {
        $this->attachInstallationKey($message);
        $message = json_encode($message);

        $bytesWritten = socket_write($this->socket, $message, strlen($message));

        if ($delay > 0) {
            $this->delay($delay);
        }

        return $bytesWritten;
    }

    public function setOption(int $level , int $option , $value): bool
    {
        return socket_set_option($this->socket, $level, $option, $value);
    }

    public function Close()
    {
        if($this->socket){
            socket_close($this->socket);
        }
        $this->connected = false;
        
        return $this;
    }

    private function delay(int $length)
    {
        usleep($length);
    }

    private function attachInstallationKey(array & $message)
    {
        $settings = Settings::getInstance();
        $message['installation_key'] = $settings->getInstallationKey();
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function __destruct(){
        if($this->socket && $this->connected){
            socket_close($this->socket);
        }
    }
}
