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
        $value = $element->getValue();
        if (!is_string($value)) {
            return false;
        }
        $element->setValue(trim($value));
        return true;
    }
    
    /**
     * @return array|null
     */
    public function getMessage() {
        return ['string'];
    }
}
