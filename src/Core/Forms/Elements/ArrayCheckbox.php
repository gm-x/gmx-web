<?php
namespace GameX\Core\Forms\Elements;

class ArrayCheckbox extends Input {

    /**
     * @var array
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
        $this->value = (array) $value;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function getType() {
        return 'checkbox';
    }

    public function getValues() {
        return $this->values;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function isChecked($value) {
        return in_array($value, $this->value, true);
    }
}
