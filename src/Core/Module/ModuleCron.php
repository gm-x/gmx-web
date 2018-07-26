<?php
namespace GameX\Core\Module;

class ModuleCron {

	/**
	 * @var string
	 */
	protected $key;

	/**
	 * @var string
	 */
	protected $controller;

	/**
	 * ModuleCron constructor.
	 * @param string $key
	 * @param string $controller
	 */
	public function __construct($key, $controller) {
		$this->key = $key;
		$this->controller = $controller;
	}

	/**
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}
}
