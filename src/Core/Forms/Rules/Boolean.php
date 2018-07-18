<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;

class Boolean extends BaseRule {
    protected function isValid(Form $form, Element $element) {
        $value = filter_var($element->getValue(), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($value === null) {
        	return false;
		}
        $element->setValue($value);
        return true;
    }
    
    public function getMessageKey() {
        return ['boolean'];
    }
}
