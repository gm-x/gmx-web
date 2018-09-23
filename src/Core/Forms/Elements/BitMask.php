<?php
namespace GameX\Core\Forms\Elements;

class BitMask extends BaseElement {

    /**
     * @var int
     */
    protected $value;
    
    /**
     * @var array
     */
    protected $values = [];
    
    /**
     * BitMask constructor.
     * @param string $name
     * @param mixed $value
     * @param array $values
     * @param array $options
     */
    public function __construct($name, $value, array $values = [], array $options = []) {
        parent::__construct($name, $value, $options);
        $this->values = $values;
    }

    /**
     * @inheritdoc
     */
    public function setValue($value) {
        $this->value = (int) $value;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @param int $value
     * @return bool
     */
    public function hasValue($value) {
        return ($this->value & $value) !== 0;
    }
    
    /**
     * @return array
     */
    public function getValues() {
        return $this->values;
    }
}
