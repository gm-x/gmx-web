<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;

class Required extends BaseRule {
    protected function isValid(Form $form, Element $element) {
        $value = $element->getValue();
        return $value !== null && !empty($value);
    }
    
    public function getMessageKey() {
        return ['required'];
    }
}
