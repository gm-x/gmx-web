<?php

namespace GameX\Core\Forms;

class ValidationResult {
    
    /**
     * @var array
     */
    protected $values;
    
    /**
     * @var array
     */
    protected $errors;
    
    /**
     * @var bool
     */
    protected $isValid;
    
    /**
     * ValidationResult constructor.
     * @param array $values
     * @param array $errors
     * @param bool $isValid
     */
    public function __construct(array $values, array $errors, $isValid) {
        $this->values = $values;
        $this->errors = $errors;
        $this->isValid = $isValid;
    }
    
    /**
     * @return array
     */
    public function getValues() {
        return $this->values;
    }
    
    /**
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public function getValue($key, $default = null) {
        return array_key_exists($key, $this->values) ? $this->values[$key] : $default;
    }
    
    /**
     * @param string $key
     * @return bool
     */
    public function hasError($key) {
        return array_key_exists($key, $this->errors) && $this->errors[$key] !== null;
    }

    /**
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * @param string $key
     * @return string|null
     */
    public function getError($key) {
        return array_key_exists($key, $this->errors) ? $this->errors[$key] : null;
    }
    
    /**
     * @return string|null
     */
    public function getFirstError() {
        foreach ($this->errors as $key => $error) {
            if ($error !== null) {
                return $key . ': ' . $error;
            }
        }
        
        return null;
    }
    
    /**
     * @return bool
     */
    public function getIsValid() {
        return $this->isValid;
    }
}
