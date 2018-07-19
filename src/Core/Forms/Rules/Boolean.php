<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;

class Boolean extends BaseRule {
    
    /**
     * @param Form $form
     * @param Element $element
     * @return bool
     */
    protected function isValid(Form $form, Element $element) {
        $value = filter_var($element->getValue(), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($value === null) {
        	return false;
		}
        $element->setValue($value);
        return true;
    }
    
    /**
     * @return array
     */
	protected function getMessage() {
        return ['boolean'];
    }
}
