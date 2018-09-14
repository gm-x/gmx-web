<?php
namespace GameX\Core\Forms\Elements;

class PermissionAccess extends BaseElement {

    /**
     * @var int
     */
    protected $value;

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
     * @param int $access
     * @return bool
     */
    public function hasAccess($access) {
        return ($this->value & $access) !== 0;
    }
}
