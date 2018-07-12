<?php
namespace GameX\Core;

class Config {
	protected static $configPath;

	protected $config;

	protected function __construct(array $config) {
		$this->config = $config;
	}

	public function get

	public static function loadFromFile($configPath) {
		if (!is_readable($configPath)) {
			throw new \Exception('Couldn\'t open file ' . $configPath);
		}

		self::$configPath = $configPath;

		$content = file_get_contents($configPath);
		if (!$content) {
			throw new \Exception('Couldn\'t read from file ' . $configPath);
		}

		$data = json_decode($content, true);
		if (json_last_error() != JSON_ERROR_NONE) {
			throw new \Exception(json_last_error_msg());
		}

		if (!is_array($data)) {
			throw new \Exception('Bad format of file ' . $configPath);
		}

		return new self($data);
	}
}
