<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;

class PasswordRepeat extends BaseRule {
    
    /**
     * @var string
     */
    protected $field;
    
    /**
     * @param string $field
     */
    public function __construct($field) {
        $this->field = (string) $field;
    }
    
    /**
     * @param Form $form
     * @param Element $element
     * @return bool
     */
    protected function isValid(Form $form, Element $element) {
        if (!$form->exists($this->field)) {
            return false;
        }
        
        return $element->getValue() === $form->get($this->field)->getValue();
    }
    
    /**
     * @return array
     */
    public function getMessageKey() {
        return ['password_repeat'];
    }
}
