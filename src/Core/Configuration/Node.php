<?php
namespace GameX\Core\Configuration;

use \GameX\Core\Configuration\Exceptions\NotFoundException;

class Node {

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var bool
     */
    protected $isModified = false;

    /**
     * Node constructor.
     * @param array $data
     */
    public function __construct(array $data) {
        $this->process($data);
    }

    /**
     * @param string $key
     * @param array|mixed $value
     * @return Node
     */
    public function set($key, $value) {
        $this->data[$key] = is_array($value) ? new Node($value) : $value;
        $this->isModified = true;
        return $this;
    }
    
    /**
     * @param string $key
     * @return Node
     * @throws NotFoundException
     */
    public function getNode($key) {
        if (!$this->existsNode($key)) {
            throw new NotFoundException();
        }
        
        return $this->data[$key];
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return Node|mixed|null
     */
    public function get($key, $default = null) {
        return $this->exists($key) ? $this->data[$key] : $default;
    }
    
    /**
     * @param string $key
     * @return bool
     */
    public function existsNode($key) {
        return $this->exists($key) && $this->data[$key] instanceof Node;
    }

	/**
	 * @param string $key
	 * @return bool
	 */
    public function exists($key) {
    	return array_key_exists($key, $this->data);
	}

	/**
	 * @param string $key
	 * @return Node
	 */
	public function remove($key) {
    	if ($this->exists($key)) {
    		unset($this->data[$key]);
    		$this->isModified = true;
		}

		return $this;
	}

    /**
     * @return array
     */
	public function keys() {
	    return array_keys($this->data);
    }

    /**
     * @return array
     */
    public function toArray() {
        $result = [];
        foreach ($this->data as $key => $value) {
            $result[$key] = ($value instanceof Node) ? $value->toArray() : $value;
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function getIsModified() {
        if ($this->isModified) {
            return true;
        }

        foreach ($this->data as $value) {
            if ($value instanceof Node && $value->getIsModified()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $data
     */
    protected function process(array $data) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->data[$key] = new self($value);
            } else {
                $this->data[$key] = $value;
            }
        }
    }
}
