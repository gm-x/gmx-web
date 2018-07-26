<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;

class IPv4 extends BaseRule {
    
    /**
     * @param Form $form
     * @param Element $element
     * @return bool
     */
    protected function isValid(Form $form, Element $element) {
        return filter_var($element->getValue(), FILTER_VALIDATE_IP, ['flags' => FILTER_FLAG_IPV4]) !== false;
    }
    
    /**
     * @return array
     */
	protected function getMessage() {
        return ['ip'];
    }
}
