<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Rule;
use \GameX\Core\Lang\Language;
use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;
use \GameX\Core\Forms\BadOptionException;

abstract class BaseRule implements Rule {
    protected $options = [];
    
    public function __construct(array $options = []) {
        $this->options = $options;
    }
    
    public function validate(Form $form, $key) {
        if (!$form->exists($key)) {
            return false;
        }
    
        try {
            return (bool)$this->isValid($form, $form->get($key));
        } catch (BadOptionException $e) {
            return false;
        }
    }
    
    public function getMessage(Language $language) {
        list ($key, $args) = $this->getMessageKey();
        return $language->format('forms', $key, $args);
    }
    
    protected function getOption($option) {
        if (!array_key_exists($option, $this->options)) {
            throw new BadOptionException();
        }
        
        return $this->options[$option];
    }
    
    abstract protected function getMessageKey();
    abstract protected function isValid(Form $form, Element $element);
}
