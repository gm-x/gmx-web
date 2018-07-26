<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Rule;
use \GameX\Core\Lang\Language;
use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;

abstract class BaseRule implements Rule {
    
    /**
     * @param Form $form
     * @param string $key
     * @return bool
     */
    public function validate(Form $form, $key) {
        if (!$form->exists($key)) {
            return false;
        }
        return (bool)$this->isValid($form, $form->get($key));
    }
    
    /**
     * @param Language $language
     * @return string
     */
    public function getError(Language $language) {
        $message = $this->getMessage();
        if ($message === null) {
        	return '';
		}
		list ($key, $args) = $message;
        return $language->format($this->getErrorSection(), $key, $args);
    }
    
    /**
     * @return string
     */
    protected function getErrorSection() {
        return 'forms';
    }
    
    /**
     * @return array|null
     */
    protected function getMessage() {
        return null;
    }
    
    /**
     * @param Form $form
     * @param Element $element
     * @return bool
     */
    abstract protected function isValid(Form $form, Element $element);
}
