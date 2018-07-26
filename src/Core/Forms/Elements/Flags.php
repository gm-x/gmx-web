<?php
namespace GameX\Core\Forms\Elements;

use \GameX\Core\AccessFlags\Helper;

class Flags extends Text {

    /**
     * @inheritdoc
     */
    public function getValue() {
        return Helper::getFlags($this->value);
    }

    /**
     * @inheritdoc
     */
    public function setValue($value) {
        if (preg_match('/^\d+$/', $value)) {
            $this->value = (int)$value;
        } else {
            $this->value = Helper::readFlags($value);
        }
        return $this;
    }

	/**
	 * @return int
	 */
    public function getFlagsInt() {
    	return $this->value;
	}
}
