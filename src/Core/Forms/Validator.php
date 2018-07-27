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
    
    protected $isValid = true;
    
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
    
    
    public function validate(array $values) {
        $isValid = true;
        $errors = array_fill_keys(array_keys($this->rules), null);
        foreach ($this->rules as $key => $rules) {
            $value = array_key_exists($key, $values) ? $values[$key] : null;
            foreach ($rules as $rule) {
                $value = $rule->validate($value, $values);
                if ($value === null) {
                    $errors[$key] = $rule->getError($this->language);
                    $isValid = false;
                    break;
                }
                $values[$key] = $value;
            }
        }
        
        return new ValidationResult($values, $errors, $isValid);
    }
}
