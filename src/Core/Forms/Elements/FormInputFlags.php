<?php
namespace GameX\Core\Forms\Elements;

use \GameX\Core\AccessFlags\Helper;

class FormInputFlags extends FormInputText {

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
        $this->value = Helper::readFlags($value);
        return $this;
    }

	/**
	 * @return int
	 */
    public function getFlagsInt() {
    	return $this->value;
	}
}
