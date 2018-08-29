<?php
namespace GameX\Core\Configuration;

use GameX\Core\Configuration\Exceptions\ConfigNotFoundException;

class Config {

    /**
     * @var string
     */
	protected $path;

    /**
     * @var Node
     */
	protected $config;

    /**
     * Config constructor.
     * @param string|null $path
     */
	public function __construct($path = null) {
        $this->path = $path !== null ? (string) $path : __DIR__ . DIRECTORY_SEPARATOR . 'default.json';
		$this->config = $this->loadFromFile();
	}

    /**
     * @param string $key
     * @param mixed|null $default
     * @return Node|mixed|null
     */
	public function get($key, $default = null) {
	    return $this->config->get($key, $default);
    }

	/**
	 * @param string $key
	 * @param string $value
	 * @return Node
	 */
    public function set($key, $value) {
	    return $this->config->set($key, $value);
    }

	/**
	 * @param string $key
	 * @return bool
	 */
    public function exists($key) {
		return $this->config->exists($key);
	}

	/**
	 * @param string $key
	 * @return Node
	 */
    public function remove($key) {
		return $this->config->remove($key);
	}

    /**
     * @return $this
     */
    public function save() {
        $this->saveToFile();
        return $this;
    }

	/**
	 * @param string $path
	 * @return Config
	 */
    public function setPath($path) {
    	$this->path = $path;
    	return $this;
	}

    /**
     * @return Node
     * @throws \Exception
     */
	protected function loadFromFile() {
		if (!is_readable($this->path)) {
			throw new ConfigNotFoundException('Couldn\'t open file ' . $this->path);
		}

		$content = file_get_contents($this->path);
		if (!$content) {
			throw new ConfigNotFoundException('Couldn\'t read from file ' . $this->path);
		}

		$data = json_decode($content, true);
		if (json_last_error() != JSON_ERROR_NONE) {
			throw new ConfigNotFoundException(json_last_error_msg());
		}

		if (!is_array($data)) {
			throw new ConfigNotFoundException('Bad format of file ' . $this->path);
		}

		return new Node($data);
	}

    /**
     * @throws \Exception
     */
	protected function saveToFile() {
        $data = json_encode(
            $this->config->toArray(),
            JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        if(file_put_contents($this->path, $data) === false) {
            throw new \Exception('Couldn\'t write from file ' . $this->path);
        }
    }
}
