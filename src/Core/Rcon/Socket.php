<?php

namespace GameX\Core\Rcon;

use \GameX\Core\Rcon\Exceptions\ConnectionException;
use \GameX\Core\Rcon\Exceptions\TimeoutException;
use \GameX\Core\Rcon\Exceptions\SocketException;

class Socket
{
	/**
	 * @var string
	 */
	protected $host;

	/**
	 * @var int
	 */
	protected $port;

	/**
	 * @var int
	 */
	protected $timeout;

	/**
	 * @var resource
	 */
	protected $socket;

	/**
	 * Socket constructor.
	 * @param string $host
	 * @param int $port
	 * @param int $timeout
	 */
	public function __construct($host, $port, $timeout)
	{
		$this->host = $host;
		$this->port = $port;
		$this->timeout = $timeout;
	}

	/**
	 * @throws ConnectionException
	 */
	public function connect() {
		$this->socket = null;
		if ( ($this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) === false ) {
			$error = socket_last_error();
			throw new ConnectionException(sprintf('Unable to create a socket %d: %s', $error, socket_strerror($error)));
		}

		socket_set_nonblock($this->socket);

		$error = NULL;
		$attempts = 0;
		$timeout = $this->timeout * 1000;  // adjust because we sleeping in 1 millisecond increments
		$connected = null;
		while (!($connected = @socket_connect($this->socket, $this->host, $this->port)) && $attempts++ < $timeout) {
			$error = socket_last_error();
			if ($error != SOCKET_EINPROGRESS && $error != SOCKET_EALREADY) {
				$this->disconnect();
				throw new ConnectionException(sprintf('Unable to connect to server %d: %s', $error, socket_strerror($error)));
			}
			usleep(1000);
		}

		if (!$connected) {
			$this->disconnect();
			$error = socket_last_error();
			throw new ConnectionException(sprintf('Unable to connect to server %d: %s', $error, socket_strerror($error)));
		}
	}

	/**
	 * Close socket connection
	 */
	public function disconnect() {
		if ($this->socket) {
			socket_close($this->socket);
			$this->socket = null;
		}
	}

	/**
	 * @param $buffer
	 * @return Buffer
	 * @throws ConnectionException
	 * @throws Exceptions\BufferUnderflowException
	 * @throws SocketException
	 * @throws TimeoutException
	 */
	public function send($buffer) {
		if ($this->socket === null) {
			$this->connect();
		}

		$read = null;
		$write = [$this->socket];
		$except = null;
		$select = socket_select($read, $write, $except, 3, null);

		if ($select === 0) {
			throw new TimeoutException('Write timeout');
		}

		if (@socket_write($this->socket, $buffer) === false ) {
			$error = @socket_last_error();
			throw new SocketException(sprintf('Unable to write to socket %d: %s', $error, socket_strerror($error)));
		}

		$packets = [];
		$answerId = null;
		$attempts = 0;
		do {
			$read = [$this->socket];
			$write = null;
			$except = null;
			$timeout = $attempts >= 1 ? 1 : 15;
			$select = @socket_select($read, $write, $except, $timeout, null);

			if ($select === 0) {
				throw new TimeoutException('Read timeout');
			}

			$attempts++;
			$buffer = @socket_read($this->socket, 1400);
			if ($buffer === false) {
				break;
			}

			$packet = new Buffer($buffer);

			if ($packet->getLong() === -1) {
				$packets[0] = $packet->getBytes($packet->getLength() - $packet->getPosition());
				break;
			}

			$id = $packet->getLong();
			if ($answerId === null) {
				$answerId = $id;
			} elseif ($answerId !== $id) {
				break;
			}

			$tmp = $packet->getByte();
			$count = $tmp & 0xF;
			$number = $tmp >> 4;

			$packets[$number] = $packet->getBytes($packet->getLength() - $packet->getPosition());

		} while ($count > count($packets) && $attempts++ < 5);

		ksort($packets, SORT_NUMERIC);

		$result = new Buffer(implode('', $packets));
		return $result;
	}
}