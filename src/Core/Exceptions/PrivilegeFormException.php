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
	protected $params;

	/**
	 * @param string $message
	 * @param string $path
	 * @param array $params
	 */
	public function __construct($message, $path, array $params = []) {
		parent::__construct($message, 0, null);
		$this->path = $path;
		$this->params = $params;
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
	public function getParams() {
		return $this->params;
	}
}
