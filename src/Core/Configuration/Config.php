<?php
namespace GameX\Core\Configuration;

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
     * @param string $path
     */
	public function __construct($path) {
        $this->path = $path;
		$this->config =$this->loadFromFile($path);
	}

    /**
     * @param string $key
     * @param mixed|null $default
     * @return Node|mixed|null
     */
	public function get($key, $default = null) {
	    return $this->config->get($key, $default);
    }

    public function set($key, $value) {
	    return $this->config->set($key, $value);
    }

    /**
     * @return $this
     */
    public function save() {
        $this->saveToFile();
        return $this;
    }

    /**
     * @return Node
     * @throws \Exception
     */
	protected function loadFromFile() {
		if (!is_readable($this->path)) {
			throw new \Exception('Couldn\'t open file ' . $this->path);
		}

		$content = file_get_contents($this->path);
		if (!$content) {
			throw new \Exception('Couldn\'t read from file ' . $this->path);
		}

		$data = json_decode($content, true);
		if (json_last_error() != JSON_ERROR_NONE) {
			throw new \Exception(json_last_error_msg());
		}

		if (!is_array($data)) {
			throw new \Exception('Bad format of file ' . $this->path);
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
