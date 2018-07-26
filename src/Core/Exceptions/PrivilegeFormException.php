<?php
namespace GameX\Core\Exceptions;

use \Exception;

class PrivilegeFormException extends Exception {

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * @param string $message
	 * @param string $path
	 * @param array $data
	 */
	public function __construct($message, $path, array $data = []) {
		parent::__construct($message, 0, null);
		$this->path = $path;
		$this->data = $data;
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}
}
