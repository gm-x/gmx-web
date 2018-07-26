<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;

class Email extends BaseRule {
    
    /**
     * @param Form $form
     * @param Element $element
     * @return bool
     */
    protected function isValid(Form $form, Element $element) {
        return filter_var($element->getValue(), FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * @return array
     */
	protected function getMessage() {
        return ['email'];
    }
}
