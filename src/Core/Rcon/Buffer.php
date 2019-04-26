<?php

namespace GameX\Core\Rcon;

use \GameX\Core\Rcon\Exceptions\BufferUnderflowException;

class Buffer
{
	/**
	 * @var bool
	 */
	protected $mbExists = false;

	/**
	 * @var int
	 */
	protected $position;

	/**
	 * @var int
	 */
	protected $length;

	/**
	 * @var int
	 */
	protected $buffer;

	/**
	 * Buffer constructor.
	 * @param string $buffer
	 */
	public function __construct($buffer)
	{
		$this->buffer = $buffer;
		$this->mbExists = function_exists('mb_substr');

		$this->position = 0;
		$this->length = $this->mbExists
			? mb_strlen($this->buffer, '8bit')
			: strlen($this->buffer);;
	}

	/**
	 * @return int
	 */
	public function getPosition() {
		return $this->position;
	}

	/**
	 * @return int
	 */
	public function getLength() {
		return $this->length;
	}

	/**
	 * @return int
	 * @throws BufferUnderflowException
	 */
	public function getByte() {
		return $this->getData('C', 1);
	}

	/**
	 * @return int
	 * @throws BufferUnderflowException
	 */
	public function getShort() {
		return $this->getData('s', 2);
	}

	/**
	 * @return int
	 * @throws BufferUnderflowException
	 */
	public function getLong() {
		return $this->getData('l', 4);
	}

	/**
	 * @return float
	 * @throws BufferUnderflowException
	 */
	public function getFloat() {
		return $this->getData('f', 4);
	}

	/**
	 * @param string $format
	 * @param int $length
	 * @return mixed
	 * @throws BufferUnderflowException
	 */
	public function getData($format, $length) {
		if (($this->length - $this->position) < $length) {
			throw new BufferUnderflowException(sprintf('Not enough data to unpack %d', $length));
		}

		$result = unpack($format, $this->getBytes($length));
		$this->position += $length;
		return $result[1];
	}

	public function skipBytes($length) {
		$this->position += $length;
	}

	/**
	 * @param int $length
	 * @return string
	 */
	public function getBytes($length = null) {
		return $this->mbExists
			? mb_substr($this->buffer, $this->position , $length, '8bit')
			: substr($this->buffer, $this->position , $length);
	}

	/**
	 * @return string
	 */
	public function getString() {
		$result = '';
		while ($this->buffer[$this->position] != "\x00") {
			$result .= $this->buffer[$this->position];
			$this->position++;

		}
		$this->position++;
		return $result;
	}
}