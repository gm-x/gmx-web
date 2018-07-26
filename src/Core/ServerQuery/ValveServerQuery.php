<?php
namespace GameX\Core\ServerQuery;

class ValveServerQuery {

	const ENGINE_GOLDSRC = 1;
	const ENGINE_SOURCE = 2;

	const HEADER_PING = 0x6A;
	const HEADER_INFO = 0x49;
	const HEADER_INFO_OLD = 0x6D;
	const HEADER_CHALLENGE = 0x41;
	const HEADER_PLAYERS = 0x44;
	const HEADER_RULES = 0x45;
	const HEADER_RCON = 0x6C;

	protected $socket;

	/**
	 * @var
	 */
	private $rconPassword;

	/**
	 * @var null
	 */
	private $rconChallenge = null;

	/**
	 * ValveServerQuery constructor.
	 * @param string $host
	 * @param array $options
	 */
	public function __construct($host, $port, array $options = array()) {
		$this->socket = new Socket(
			$host,
			$port,
			array_key_exists('timeout', $options) ? (int) $options['timeout'] : 10,
			array_key_exists('engine', $options) ? (int) $options['engine'] : ValveServerQuery::ENGINE_GOLDSRC
		);

		if (array_key_exists('rcon', $options)) {
			$this->rconPassword = $options['rcon'];
		}
	}

	public function __destruct() {
		$this->socket->disconnect();
	}

	public function getAddress() {
		return $this->host . ':' . $this->port;
	}

	/**
	 * Ping server
	 *
	 * @return bool
	 */
	public function ping() {
		$buffer = $this->socket->send("\xFF\xFF\xFF\xFF\x69");
		return $buffer->getByte() == self::HEADER_PING;
	}

	/**
	 * Get server info
	 *
	 * @return array
	 * @throws ValveServerQueryException
	 */
	public function getInfo() {
		$buffer = $this->socket->send("\xFF\xFF\xFF\xFF\x54\x53\x6F\x75\x72\x63\x65\x20\x45\x6E\x67\x69\x6E\x65\x20\x51\x75\x65\x72\x79\x00", true);
		$result = array();
		$header = $buffer->getByte();
		switch ($header) {
			case self::HEADER_INFO_OLD:
				$result['address'] = $buffer->getString();
				$result['name'] = $buffer->getString();
				$result['map'] = $buffer->getString();
				$result['folder'] = $buffer->getString();
				$result['game'] = $buffer->getString();
				$result['players'] = $buffer->getByte();
				$result['maxPlayers'] = $buffer->getByte();
				$result['protocol'] = $buffer->getByte();
				$result['dedicated'] = $this->getDedicated($buffer);
				$result['os'] = $this->getOS($buffer);
				$result['visibility'] = $buffer->getByte() === 1;
				$mod = $buffer->getByte() === 1;
				if ($mod) {
					$result['link'] = $buffer->getString();
					$result['downloadLink'] = $buffer->getString();
					$buffer->skipBytes(1);
					$result['version'] = $buffer->getLong();
					$result['size'] = $buffer->getLong();
					$result['multiplayer'] = $buffer->getByte() === 1;
					$result['ownDll'] = $buffer->getByte() === 1;
				} else {
					$result['link'] = '';
					$result['downloadLink'] = '';
					$result['version'] = 0;
					$result['size'] = 0;
					$result['multiplayer'] = false;
					$result['ownDll'] = false;
				}
				$result['vac'] = $buffer->getByte() === 1;
				$result['bots'] = $buffer->getByte();
				break;

			case self::HEADER_INFO:
				$result['protocol'] = $buffer->getByte();
				$result['name'] = $buffer->getString();
				$result['map'] = $buffer->getString();
				$result['folder'] = $buffer->getString();
				$result['game'] = $buffer->getString();
				$result['gameId'] = $buffer->getShort();
				$result['players'] = $buffer->getByte();
				$result['maxPlayers'] = $buffer->getByte();
				$result['bots'] = $buffer->getByte();
				$result['dedicated'] = $this->getDedicated($buffer);
				$result['os'] = $this->getOS($buffer);
				$result['visibility'] = $buffer->getByte() === 1;
				$result['vac'] = $buffer->getByte() === 1;
				$result['version'] = $buffer->getString();
				$extraFlag = $buffer->getByte();
				if ($extraFlag & 0x80) {
					$result['port'] = $buffer->getShort();
				}
				if ($extraFlag & 0x10) {
					if (PHP_INT_SIZE === 8 || extension_loaded('gmp')) {
						if (PHP_INT_SIZE === 8) {
							$result['steamID'] = $buffer->getLong() | ($buffer->getLong() << 32);
						} else {
							$steamIDLower = gmp_abs($buffer->getLong());
							$steamIDInstance = gmp_mul(gmp_abs($buffer->getLong()), gmp_pow(2, 32));
							$result['steamID'] = gmp_strval(gmp_or($steamIDLower, $steamIDInstance));
							unset($steamIDLower, $steamIDInstance);
						}
					} else {
						$result['steamID'] = 0;
						$buffer->skipBytes(8);
					}
				}
				if ($extraFlag & 0x40) {
					$result['sourceTV'] = array(
						'port' => $buffer->getShort(),
						'name' => $buffer->getString()
					);
				}
				if ($extraFlag & 0x20) {
					$result['keywords'] = $buffer->getString();
				}
				if ($extraFlag & 0x01) {
					if (PHP_INT_SIZE === 8 || extension_loaded('gmp')) {
						if (PHP_INT_SIZE === 8) {
							$result['gameId'] = $buffer->getLong() | ($buffer->getLong() << 32);
						} else {
							$gameIDLower = gmp_abs($buffer->getLong());
							$gameIDInstance = gmp_mul(gmp_abs($buffer->getLong()), gmp_pow(2, 32));
							$result['gameId'] = gmp_strval(gmp_or($gameIDLower, $gameIDInstance));
							unset($gameIDLower, $gameIDInstance);
						}
					} else {
						$result['gameId'] = 0;
						$buffer->skipBytes(8);
					}
				}
				break;

			default:
				throw new ValveServerQueryException(sprintf(
					'Bad header \x%X in packet. Need to be \x%X or \x%X',
					$header,
					self::HEADER_INFO,
					self::HEADER_INFO_OLD

				), ValveServerQueryException::ERROR_BAD_HEADER);
		}

		return array(
			'protocol' => $result['protocol'],
			'name' => $result['name'],
			'map' => $result['map'],
			'players' => $result['players'],
			'maxPlayers' => $result['maxPlayers'],
			'bots' => $result['bots'],
			'game' => $result['game'],
			'dedicated' => $result['dedicated'],
			'os' => $result['os'],
			'vac' => $result['vac'],
			'extra' => $result
		);
	}

	/**
	 * Get server players
	 *
	 * @return array
	 * @throws ValveServerQueryException
	 */
	public function getPlayers() {
		$buffer = $this->socket->send("\xFF\xFF\xFF\xFF\x55\xFF\xFF\xFF\xFF"); // Send challenge
		$header = $buffer->getByte();
		if ($header != self::HEADER_CHALLENGE) {
			throw new ValveServerQueryException(sprintf(
				'Bad header \x%X in packet. Need to be \x%X',
				$header,
				self::HEADER_CHALLENGE
			), ValveServerQueryException::ERROR_BAD_HEADER);
		}
		$buffer = $this->socket->send("\xFF\xFF\xFF\xFF\x55" . $buffer->getBytes(4));

		$header = $buffer->getByte();
		if ($header != self::HEADER_PLAYERS) {
			throw new ValveServerQueryException(sprintf(
				'Bad header \x%X in packet. Need to be \x%X',
				$header,
				self::HEADER_PLAYERS
			), ValveServerQueryException::ERROR_BAD_HEADER);
		}
		$players = $buffer->getByte();
		$result = array();
		for ($i = 0; $i < $players; $i++) {
			$tmp = array();
			$tmp['id'] = $buffer->getByte();
			$tmp['name'] = $buffer->getString();
			$tmp['score'] = $buffer->getLong();
			$tmp['time'] = (int)$buffer->getFloat();
			$tmp['timeFormatted'] = gmdate('H:i:s', $tmp['time']);
			$result[] = $tmp;
		}
		return $result;
	}

	/**
	 * Get server rules
	 *
	 * @return array
	 * @throws ValveServerQueryException
	 */
	public function getRules() {
		$buffer = $this->socket->send("\xFF\xFF\xFF\xFF\x56\xFF\xFF\xFF\xFF"); // Send challenge
		$header = $buffer->getByte();
		if ($header != self::HEADER_CHALLENGE) {
			throw new ValveServerQueryException(sprintf(
				'Bad header \x%X in packet. Need to be \x%X',
				$header,
				self::HEADER_CHALLENGE
			), ValveServerQueryException::ERROR_BAD_HEADER);
		}
		$buffer = $this->socket->send("\xFF\xFF\xFF\xFF\x56" . $buffer->getBytes(4));

		$header = $buffer->getByte();
		if ($header != self::HEADER_RULES) {
			throw new ValveServerQueryException(sprintf(
				'Bad header \x%X in packet. Need to be \x%X',
				$header,
				self::HEADER_RULES
			), ValveServerQueryException::ERROR_BAD_HEADER);
		}
		$rules = $buffer->getByte();
		$result = array();
		for ($i = 0; $i < $rules; $i++) {
			$value = $buffer->getString();
			$name = $buffer->getString();
			$result[$name] = $value;
		}
		return $result;
	}

	public function rcon($cmd) {
		if (empty($this->rconPassword)) {
			throw new ValveServerQueryException('Bad rcon password.', ValveServerQueryException::ERROR_RCON);
		}

		if ($this->rconChallenge === null) {
			$buffer = $this->socket->send("\xFF\xFF\xFF\xFF\x63\x68\x61\x6C\x6C\x65\x6E\x67\x65\x20\x72\x63\x6F\x6E");
			if ($buffer->getBytes(14) !== 'challenge rcon') {
				throw new ValveServerQueryException('Failed to get RCON challenge.', ValveServerQueryException::ERROR_RCON);
			}
			$buffer->skipBytes(15);
			$this->rconChallenge = trim($buffer->getBytes(-2));
		}

		$buffer = $this->socket->send(sprintf("\xFF\xFF\xFF\xFFrcon %s %s %s\0", $this->rconChallenge, $this->rconPassword, $cmd));
		$header = $buffer->getByte();
		if ($header != self::HEADER_RCON) {
			throw new ValveServerQueryException(sprintf(
				'Bad header \x%X in packet. Need to be \x%X',
				$header,
				self::HEADER_RCON
			), ValveServerQueryException::ERROR_BAD_HEADER);
		}

		$result = trim($buffer->getBytes(-1));

		if ($result === 'Bad rcon_password.') {
			throw new ValveServerQueryException('Bad rcon password.', ValveServerQueryException::ERROR_RCON);
		}

		if ($result === 'You have been banned from this server.') {
			throw new ValveServerQueryException('You have been banned from this server.', ValveServerQueryException::ERROR_RCON);
		}

		return $result;
	}

	private function getDedicated(Buffer $buffer) {
		switch (chr($buffer->getByte())) {
			case 'd':
			case 'D':
				return 'dedicated';
			case 'l':
			case 'L':
				return 'local';
			case 'p':
			case 'P':
				return 'HLTV';
			default:
				return 'unknown';
		}
	}

	private function getOS(Buffer $buffer) {
		switch (chr($buffer->getByte())) {
			case 'l':
			case 'L':
				return 'linux';
			case 'w':
			case 'W':
				return'windows';
			case 'm':
			case 'o':
			case 'M':
			case 'O':
				return 'mac';
				break;
			default:
				return 'unknown';
		}
	}
}
