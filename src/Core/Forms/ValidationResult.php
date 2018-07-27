<?php

namespace GameX\Core\Forms;

class ValidationResult {
    protected $values;
    protected $errors;
    protected $isValid;
    
    public function __construct(array $values, array $errors, $isValid) {
        $this->values = $values;
        $this->errors = $errors;
        $this->isValid = $isValid;
    }
    
    public function getValue($key, $default = null) {
        return array_key_exists($key, $this->values) ? $this->values[$key] : $default;
    }
    
    public function hasError($key) {
        return array_key_exists($key, $this->errors) && $this->errors[$key] !== null;
    }
    
    public function getError($key) {
        return array_key_exists($key, $this->errors) ? $this->errors[$key] : null;
    }
    
    public function getIsValid() {
        return $this->isValid;
    }
}
