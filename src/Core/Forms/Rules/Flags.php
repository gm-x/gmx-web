<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;

class Flags extends BaseRule {
    
    /**
     * @param Form $form
     * @param Element $element
     * @return bool
     */
    protected function isValid(Form $form, Element $element) {
        return (bool) preg_match('/^[a-z]+$/', $element->getValue());
    }
    
    /**
     * @return array
     */
    public function getMessage() {
        return ['regexp'];
    }
}
