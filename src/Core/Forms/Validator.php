<?php

namespace GameX\Core\Forms;

use \GameX\Core\Lang\Language;

class Validator {
    /**
     * @var Language
     */
    protected $language;
    /**
     * @var Rule[][]
     */
    protected $rules = [];
    
    /**
     * @param Language $language
     */
    public function __construct(Language $language) {
        $this->language = $language;
    }
    
    /**
     * @param $key
     * @param Rule $rule
     * @return Validator
     */
    public function add($key, Rule $rule) {
        if (!array_key_exists($key, $this->rules)) {
            $this->rules[$key] = [];
        }
        
        $this->rules[$key][] = $rule;
        return $this;
    }
    
    /**
     * @param Form $form
     * @return bool
     */
    public function validate(Form $form) {
        $elements = $form->getElements();
        $isValid = true;
        foreach ($elements as $element) {
            if (!array_key_exists($element->getName(), $this->rules)) {
                continue;
            }
            $name = $element->getName();
            foreach ($this->rules[$name] as $rule) {
                if(!$rule->validate($form, $name)) {
                    $isValid = false;
                    $element
                        ->setHasError(true)
                        ->setError($rule->getError($this->language));
                    break;
                }
            }
        }
        
        return $isValid;
    }
}
