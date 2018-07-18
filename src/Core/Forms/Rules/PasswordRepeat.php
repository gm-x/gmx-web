<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;

class PasswordRepeat extends BaseRule {
    protected function isValid(Form $form, Element $element) {
        $repeat = $form->get($this->getOption('element'));
        if (!$repeat) {
            return false;
        }
        
        return $element->getValue() === $repeat->getValue();
    }
    
    public function getMessageKey() {
        return ['password_repeat'];
    }
}
