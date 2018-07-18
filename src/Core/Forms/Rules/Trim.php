<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;

class Trim extends BaseRule {
    protected function isValid(Form $form, Element $element) {
        $element->setValue(trim($element->getValue()));
        return true;
    }
    
    public function getMessageKey() {
        return [];
    }
}
