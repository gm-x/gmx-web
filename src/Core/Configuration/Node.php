<?php
namespace GameX\Core\Configuration;

class Node {
    /**
     * @var array
     */
    protected $data = [];

    /**
     * Node constructor.
     * @param array $data
     */
    public function __construct(array $data) {
        $this->process($data);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value) {
        $this->data[$key] = is_array($value) ? new self($value) : $value;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return Node|mixed|null
     */
    public function get($key, $default = null) {
        return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
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
