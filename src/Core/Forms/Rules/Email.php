<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;

class Email extends BaseRule {
    protected function isValid(Form $form, Element $element) {
        return filter_var($element->getValue(), FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public function getMessageKey() {
        return ['email'];
    }
}
