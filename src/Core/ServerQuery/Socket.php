<?php
namespace GameX\Core\ServerQuery;

class Socket {

	/**
	 * @var resource
	 */
	protected $socket = null;

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
	 * @var int
	 */
	protected $engine;

	/**
	 * Socket constructor.
	 * @param string $host
	 * @param int $port
	 * @param int $timeout
	 * @param int $engine
	 */
	public function __construct($host, $port, $timeout, $engine) {
		$this->host = $host;
		$this->port = $port;
		$this->timeout = $timeout;
		$this->engine = $engine;
	}

	/**
	 * Open socket connection
	 *
	 * @throws ValveServerQueryException
	 */
	public function connect() {
		$this->socket = null;
		if ( ($this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) === false ) {
			throw new ValveServerQueryException(sprintf(
				'Unable to create a socket: %s',
				socket_strerror(socket_last_error())
			), ValveServerQueryException::ERROR_SOCKET);
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
				throw new ValveServerQueryException(sprintf(
					'Unable to connect to server %s:%d: %s',
					$this->host,
					$this->port,
					socket_strerror(socket_last_error())
				), ValveServerQueryException::ERROR_CONNECT);
			}
			usleep(1000);
		}

		if (!$connected) {
			$this->disconnect();
			throw new ValveServerQueryException(sprintf(
				'Unable to connect to server %s:%d: %s',
				$this->host,
				$this->port,
				socket_strerror(socket_last_error())
			), ValveServerQueryException::ERROR_CONNECT);
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
	 * @param string $sendBuffer
	 * @param bool $waitForSecondPacket
	 * @return Buffer
	 * @throws ValveServerQueryException
	 */
	public function send($sendBuffer, $waitForSecondPacket = false) {
		if ($this->socket === null) {
			$this->connect();
		}

		$buffer = new Buffer();

		$read = null;
		$write = [$this->socket];
		$except = null;
		$select = socket_select($read, $write, $except, 3, null);

		if ($select === 0) {
			throw new ValveServerQueryException('Write timeout', ValveServerQueryException::ERROR_WRITE);
		}

		if (socket_write($this->socket, $sendBuffer) === false ) {
			throw new ValveServerQueryException(sprintf(
				'Unable to write to socket: %s',
				socket_strerror(socket_last_error())
			), ValveServerQueryException::ERROR_WRITE);
		}

		$packets = [];
		$answerId = null;
		$attempts = 0;
		$compressed = false;
		$checksum = null;
		do {
			$read = [$this->socket];
			$write = null;
			$except = null;
			$timeout = $waitForSecondPacket && $attempts >= 1 ? 1 : 15;
			$select = socket_select($read, $write, $except, $timeout, null);

			if ($select === 0) {
				if ($waitForSecondPacket) {
					break;
				} else {
					throw new ValveServerQueryException('Read timeout', ValveServerQueryException::ERROR_READ);
				}
			}

			$readBuffer = socket_read($this->socket, 1400);
			if ($readBuffer === false) {
				break;
			}

			$buffer->setBuffer($readBuffer);

			$header = $buffer->getLong();
			if ($header === -1) {
				$packets[0] = $readBuffer;

				$attempts++;
				if ($waitForSecondPacket && $attempts < 2) {
					$count = 2;
					continue;
				} else {
					break;
				}
			}

			$id = $buffer->getLong();
			if ($answerId === null) {
				$answerId = $id;
			} elseif ($answerId !== $id) {
				break;
			}

			if ($this->engine === ValveServerQuery::ENGINE_SOURCE) {
				$compressed = ($answerId & 0x80000000) !== 0;
				$count = $buffer->getByte();
				$number = $buffer->getByte() + 1;
				if ($compressed) {
					$buffer->skipBytes(4);
					$checksum = (string)$buffer->getLong();
				} else {
					$buffer->skipBytes(2);
				}
			} else {
				$tmp = $buffer->getByte();
				$count = $tmp & 0xF;
				$number = $tmp >> 4;
			}


			$packets[$number] = $buffer->getBytes($buffer->getLength() - $buffer->getPosition());

		} while ($count > count($packets) && $attempts++ < 5);

		ksort($packets, SORT_NUMERIC);

		$tmpBuffer = implode('', $packets);


		if ($compressed) {
			if (!function_exists('bzdecompress')) {
				throw new ValveServerQueryException(
					'Received compressed packet, PHP doesn\'t have Bzip2 library installed, can\'t decompress.',
					ValveServerQueryException::ERROR_DECOMPRESS
				);
			}

			$tmpBuffer = bzdecompress($tmpBuffer);

			if (sprintf('%u', crc32($tmpBuffer)) !== $checksum) {
				throw  new ValveServerQueryException('CRC32 checksum mismatch of uncompressed packet data',
					ValveServerQueryException::ERROR_DECOMPRESS
				);
			}
		}

		$buffer->setBuffer($tmpBuffer);
		$buffer->skipBytes(4);

		unset($sendBuffer, $readBuffer, $tmpBuffer, $read, $write, $attempts, $select, $packets, $count, $number, $answerId, $compressed, $checksum);

		return $buffer;
	}
}
