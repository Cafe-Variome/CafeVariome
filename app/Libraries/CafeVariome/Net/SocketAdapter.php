<?php namespace App\Libraries\CafeVariome\Net;

/**
 * SocketAdapter.php
 * Created: 16/02/2021
 *
 * @author Mehdi Mehtarizadeh
 *
 */

use App\Libraries\CafeVariome\Net\Service\IMessage;

class SocketAdapter
{
	/**
	 * @var string the address that the socket needs to be bound to
	 */
    private string $address;

	/**
	 * @var int the port that the socket needs to be bound to
	 */
    private int $port;

	/**
	 * @var \Socket socket resource
	 */
    private \Socket|false $socket;

	private $reserveSocket;

    private array $defaultConfig;
    private bool $connected;

	private bool $locked; // Boolean flag to show if a request is being served.

    public function __construct(string $address, int $port)
	{
        $this->address = $address;
        $this->port = $port;
        $this->defaultConfig = ['domain' => AF_INET, 'type' => SOCK_STREAM, 'protocol' => SOL_TCP];
        $this->connected = false;
		$this->locked = false;
		$this->socket = false;
    }

    /**
     * @param array $config: domain, type, and protocol configuration
     */
    public function Create(array $config = null)
    {
        if (!$config || count($config) == 0)
		{
            $config = $this->defaultConfig;
        }
        $this->socket = socket_create($config['domain'], $config['type'], $config['protocol']);
        return $this;
    }

	public function Bind()
	{
		socket_bind($this->socket, $this->address, $this->port);
	}

    public function Connect()
    {
        $this->connected = @socket_connect($this->socket, $this->address, $this->port);
        return $this;
    }

	public function Accept()
	{
		$this->reserveSocket = $this->socket;
		$this->socket = socket_accept($this->socket);
		$this->locked = true;
	}

    public function Read(int $length, int $delay = 0, int $type = PHP_BINARY_READ)
    {
        $input = socket_read($this->socket, $length, $type);

        if ($delay > 0) {
            $this->delay($delay);
        }

        return $input;
    }

	public function Send(array $message, int $delay = 0, int $flags = MSG_EOR)
	{
		$message = json_encode($message);

		$bytesSent = socket_send($this->socket, $message , strlen($message), MSG_EOR);

		if ($delay > 0) {
			$this->delay($delay);
		}

		return $bytesSent;
	}

    public function Write(IMessage $message, int $delay = 0)
    {
        $jsonMessage = $message->ToJson();

        $bytesWritten = socket_write($this->socket, $jsonMessage, strlen($jsonMessage));

        if ($delay > 0)
		{
            $this->delay($delay);
        }

        return $bytesWritten;
    }

	public function Listen()
	{
		socket_listen($this->socket);
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

		if ($this->locked){
			$this->socket = $this->reserveSocket;
			$this->reserveSocket = null;
			$this->locked = false;
		}

        $this->connected = false;

        return $this;
    }

    private function delay(int $length)
    {
        usleep($length);
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function __destruct()
	{
        if($this->socket && $this->connected)
		{
            socket_close($this->socket);
        }
    }
}
