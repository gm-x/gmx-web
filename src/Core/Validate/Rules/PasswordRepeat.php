<?php
namespace GameX\Core\Validate\Rules;

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
     * @inheritdoc
     */
    public function validate($value, array $values) {
        if (!array_key_exists($this->field, $values)) {
            return null;
        }
        
        return ($value === $values[$this->field]) ? $value : null;
    }
    
    /**
     * @return array
     */
    public function getMessage() {
        return ['password_repeat'];
    }
}
