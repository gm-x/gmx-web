<?php

namespace GameX\Core\Rcon;

use \GameX\Core\Rcon\Exceptions\ChallengeException;
use \GameX\Core\Rcon\Exceptions\PacketException;

class Rcon
{
	const HEADER_RCON = 0x6C;

	/**
	 * @var Socket
	 */
	protected $socket;

	/**
	 * @var
	 */
	protected $password;

	/**
	 * @var string|null
	 */
	protected $challenge = null;

	/**
	 * Rcon constructor.
	 * @param string $host
	 * @param int $port
	 * @param string $password
	 * @param array $options
	 */
	public function __construct($host, $port, $password, array $options = [])
	{
		$timeout = array_key_exists('timeout', $options) ? (int) $options['timeout'] : 1;
		$this->socket = new Socket($host, $port, $timeout);

		$this->password = $password;
	}

	public function execute($command) {
		if ($this->challenge === null) {
			$buffer = $this->socket->send("\xFF\xFF\xFF\xFF\x63\x68\x61\x6C\x6C\x65\x6E\x67\x65\x20\x72\x63\x6F\x6E");
			if ($buffer->getBytes(14) !== 'challenge rcon') {
				throw new ChallengeException('Failed to get RCON challenge.');
			}
			$buffer->skipBytes(15);
			$this->challenge = trim($buffer->getBytes(-2));
		}

		$buffer = $this->socket->send(sprintf("\xFF\xFF\xFF\xFFrcon %s %s %s\0", $this->challenge, $this->password, $command));
		if ($buffer->getByte() != self::HEADER_RCON) {
			throw new PacketException(sprintf('Bad header \x%X in packet. Need to be \x%X', $header, self::HEADER_RCON));
		}

		$result = trim($buffer->getBytes(-1));

		if ($result === 'Bad rcon_password.') {
			throw new PacketException('Bad rcon password.');
		}

		if ($result === 'You have been banned from this server.') {
			throw new PacketException('You have been banned from this server.');
		}

		return $result;
	}
}