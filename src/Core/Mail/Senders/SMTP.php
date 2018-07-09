<?php
namespace GameX\Core\Mail\Senders;

use \GameX\Core\Mail\Sender;
use \GameX\Core\Mail\Message;
use \GameX\Core\Mail\Exceptions\ConnectException;
use \GameX\Core\Mail\Exceptions\CodeException;
use \GameX\Core\Mail\Exceptions\CryptoException;
use Symfony\Component\Config\Definition\Exception\Exception;

class SMTP implements Sender {
	/**
	 * smtp socket
	 */
	protected $smtp;

	/**
	 * smtp server
	 */
	protected $host;

	/**
	 * smtp server port
	 */
	protected $port;

	/**
	 * smtp secure ssl tls
	 */
	protected $secure;

	/**
	 * EHLO message
	 */
	protected $ehlo;

	/**
	 * smtp username
	 */
	protected $username;

	/**
	 * smtp password
	 */
	protected $password;

	/**
	 * oauth access token
	 */
	protected $oauthToken;

	/**
	 * @var array
	 */
	protected $commandStack = [];

	/**
	 * @var array
	 */
	protected $resultStack = [];

	public function __construct($host, $port, $secure = null, $username = null, $password = null) {
		$this->host = $host;
		$this->port = $port;
		$this->secure = $secure;
		$this->username = $username;
		$this->password = $password;
	}

	public function send(Message $message) {
		$this->commandStack = [];
		$this->resultStack = [];

		$socket = $this->connect();
		try {
			$this->ehlo($socket);

			if ($this->secure === 'tls') {
				$this->startTLS($socket);
				$this->ehlo($socket);
			}

			if ($this->username !== null || $this->password !== null) {
				$this->auth($socket);
			}

			$in = 'MAIL FROM:<' . $message->getFrom()->getEmail() . '>' . Message::CRLF;
			$this->pushStack($socket, $in);
			$code = $this->getCode($socket);
			if ($code !== '250') {
				throw new CodeException('250', $code, array_pop($this->resultStack));
			}

			/** @var \GameX\Core\Mail\Email[] $emails */
			$emails = array_merge(
				$message->getTo(),
				$message->getCc(),
				$message->getBcc()
			);
			foreach ($emails as $email) {
				$in = 'RCPT TO:<' . $email->getEmail() . '>' . Message::CRLF;
				$this->pushStack($socket, $in);
				$code = $this->getCode($socket);
				if ($code !== '250') {
					throw new CodeException('250', $code, array_pop($this->resultStack));
				}
			}

			$in = 'DATA' . Message::CRLF;
			$this->pushStack($socket, $in);
			$code = $this->getCode($socket);
			if ($code !== '354') {
				throw new CodeException('354', $code, array_pop($this->resultStack));
			}
			$this->pushStack($socket, $this->getData($message));
			$code = $this->getCode($socket);
			if ($code !== '250') {
				throw new CodeException('250', $code, array_pop($this->resultStack));
			}

			$in = 'QUIT' . Message::CRLF;
			$this->pushStack($socket, $in);
			$code = $this->getCode($socket);
			if ($code !== '221') {
				throw new CodeException('221', $code, array_pop($this->resultStack));
			}

			fclose($socket);
		} catch (Exception $e) {
			fclose($socket);
			throw $e;
		}
	}

	public function connect() {
		$host = ($this->secure == 'ssl' ? 'ssl://' : '') . $this->host;
		$socket = @fsockopen($host, $this->port);
		//set block mode
		//    stream_set_blocking($this->smtp, 1);
		if (!$socket) {
			throw new ConnectException('Could not open SMTP Port.');
		}
		$code = $this->getCode($socket);
		if ($code !== '220') {
			fclose($socket);
			throw new CodeException('220', $code, array_pop($this->resultStack));
		}
		return $socket;
	}

	protected function ehlo($socket) {
		$in = 'EHLO ' . $this->host . Message::CRLF;
		$this->pushStack($socket, $in);
		$code = $this->getCode($socket);
		if ($code !== '250') {
			throw new CodeException('250', $code, array_pop($this->resultStack));
		}
		return $this;
	}

	protected function startTLS($socket) {
		$in = 'STARTTLS' . Message::CRLF;
		$this->pushStack($socket, $in);
		$code = $this->getCode($socket);
		if ($code !== '220') {
			throw new CodeException('220', $code, array_pop($this->resultStack));
		}
		if (!stream_socket_enable_crypto($this->smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
			throw new CryptoException('Start TLS failed to enable crypto');
		}
	}

	protected function auth($socket) {
		$in = "AUTH LOGIN" . Message::CRLF;
		$this->pushStack($socket, $in);
		$code = $this->getCode($socket);
		if ($code !== '334') {
			throw new CodeException('334', $code, array_pop($this->resultStack));
		}
		$in = base64_encode($this->username) . Message::CRLF;
		$this->pushStack($socket, $in);
		$code = $this->getCode($socket);
		if ($code !== '334') {
			throw new CodeException('334', $code, array_pop($this->resultStack));
		}
		$in = base64_encode($this->password) . Message::CRLF;
		$this->pushStack($socket, $in);
		$code = $this->getCode($socket);
		if ($code !== '235') {
			throw new CodeException('235', $code, array_pop($this->resultStack));
		}
	}

	protected function getData(Message $message) {
		$in = '';
		$in .= 'From: ' . $message->getFrom();
		$in .= 'Subject: ' . $message->getSubject();
		$headers = $message->getHeaders();
		foreach ($headers as $key => $value) {
			$in .= $key . ': ' . $value . Message::CRLF;
		}
		$in .= $message->getMessage();
		$in .= Message::CRLF . Message::CRLF . '.' . Message::CRLF;
		return $in;
	}

	protected function pushStack($socket, $string) {
		$this->commandStack[] = $string;
		fputs($socket, $string, strlen($string));
	}

	protected function getCode($socket) {
		while ($str = fgets($socket, 515)) {
			$this->resultStack[] = $str;
			if(substr($str,3,1) == " ") {
				$code = substr($str,0,3);
				return $code;
			}
		}
		throw new ConnectException('SMTP Server did not respond with anything I recognized');
	}
}
