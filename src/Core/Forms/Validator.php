<?php

namespace GameX\Core\Forms;

use \GameX\Core\Lang\Language;

class Validator {
    /**
     * @var Language
     */
    protected $language;
    
    /**
     * @var bool[]
     */
    protected $required = [];
    
    /**
     * @var Rule[][]
     */
    protected $rules = [];
    
    /**
     * @var bool
     */
    protected $isValid = true;
    
    /**
     * @param Language $language
     */
    public function __construct(Language $language) {
        $this->language = $language;
    }
    
    /**
     * @param string $key
     * @param bool $required
     * @param array $rules
     * @return Validator
     * @throws \Exception
     */
    public function set($key, $required, array $rules = []) {
        foreach ($rules as $rule) {
            if (!($rule instanceof Rule)) {
                throw new \Exception('Rules must implement Rule interface');
            }
        }
        
        $this->required = (bool) $required;
        $this->rules[$key] = $rules;
        
        return $this;
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
        if (!array_key_exists($key, $this->required)) {
            $this->required[$key] = false;
        }
        
        // TODO: Remove this
        if ($rule instanceof \GameX\Core\Forms\Rules\Trim) {
            //
        } elseif ($rule instanceof \GameX\Core\Forms\Rules\Required) {
            $this->required[$key] = true;
        } else {
            $this->rules[$key][] = $rule;
        }
    
        return $this;
    }
    
    /**
     * @param array $values
     * @return ValidationResult
     */
    public function validate(array $values) {
        $isValid = true;
        $errors = array_fill_keys(array_keys($this->rules), null);
        foreach ($this->rules as $key => $rules) {
            $value = array_key_exists($key, $values) ? trim($values[$key]) : null;
            if ($value !== null && strlen($value) > 0) {
                foreach ($rules as $rule) {
                    $value = $rule->validate($value, $values);
                    if ($value === null) {
                        $errors[$key] = $rule->getError($this->language);
                        $isValid = false;
                        break;
                    }
                }
            } elseif ($this->required[$key]) {
                $errors[$key] = $this->language->format('forms', 'required');
                $isValid = false;
                $value = null;
            } else {
                $value = null;
            }
            $values[$key] = $value;
        }
        
        return new ValidationResult($values, $errors, $isValid);
    }
}
