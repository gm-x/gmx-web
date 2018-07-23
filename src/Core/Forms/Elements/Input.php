<?php
namespace GameX\Core\Forms\Elements;

abstract class Input extends BaseElement {

    /**
     * @var string
     */
    protected $value;

    /**
     * @inheritdoc
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function setValue($value) {
        $this->value = $value;
        return $this;
    }
    
    public function getType() {
        return 'text';
    }
}
