<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;

class Trim extends BaseRule {
    
    /**
     * @param Form $form
     * @param Element $element
     * @return bool
     */
    protected function isValid(Form $form, Element $element) {
        $element->setValue(trim($element->getValue()));
        return true;
    }
    
    /**
     * @return array
     */
    public function getMessageKey() {
        return [];
    }
}
