<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;

class Required extends BaseRule {
    
    /**
     * @param Form $form
     * @param Element $element
     * @return bool
     */
    protected function isValid(Form $form, Element $element) {
        $value = $element->getValue();
        return $value !== null && !empty($value);
    }
    
    /**
     * @return array
     */
    public function getMessageKey() {
        return ['required'];
    }
}
