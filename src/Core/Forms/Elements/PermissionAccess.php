<?php
namespace GameX\Core\Forms\Elements;

class PermissionAccess extends Text {

    /**
     * @inheritdoc
     */
    public function setValue($value) {
        $this->value = (int) $value;
        return $this;
    }
    
    public function hasAccess($access) {
        return ($this->value & $access) !== 0;
    }
}
