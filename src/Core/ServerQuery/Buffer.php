<?php

namespace GameX\Core\ServerQuery;

class Buffer {

	/**
	 * @var bool
	 */
	protected $mbExists = false;

	/**
	 * @var string
	 */
	protected $buffer = '';

	/**
	 * @var int
	 */
	protected $position = 0;

	/**
	 * @var int|null
	 */
	protected $length = null;

	public function __construct() {
		$this->mbExists = function_exists('mb_substr');
	}

	public function setBuffer($buffer) {
		$this->buffer = $buffer;
		$this->length = null;
		$this->position = 0;
		return $this;
	}

	public function getBuufer() {
		return $this->buffer;
	}

	public function getPosition() {
		return $this->position;
	}

	public function setPosition($position) {
		$this->position = $position;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getLength() {
		if ($this->length === null) {
			$this->length = $this->mbExists ? mb_strlen($this->buffer, '8bit') : strlen($this->buffer);
		}

		return $this->length;
	}

	/**
	 * @return int
	 */
	public function getByte() {
		return $this->getData('C', 1);
	}

	/**
	 * @return int
	 */
	public function getShort() {
		return $this->getData('s', 2);
	}

	/**
	 * @return int
	 */
	public function getLong() {
		return $this->getData('l', 4);
	}

	/**
	 * @return float
	 */
	public function getFloat() {
		return $this->getData('f', 4);
	}

	public function skipBytes($length) {
		$this->position += $length;
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
	 * @param string $format
	 * @param int $length
	 * @return mixed
	 * @throws ValveServerQueryException
	 */
	protected function getData($format, $length) {
		if (($this->getLength() - $this->getPosition()) < $length) {
			throw new ValveServerQueryException(sprintf(
				'Not enough data to unpack %d',
				$length
			), ValveServerQueryException::ERROR_DECODE);
		}

		$result = unpack($format, $this->getBytes($length));
		$this->skipBytes($length);
		return $result[1];
	}
}
