<?php
namespace GameX\Core\Forms\Rules;

class Flags extends BaseRule {
    
    /**
     * @inheritdoc
     */
    public function validate($value, array $values) {
        return preg_match('/^[a-z]+$/', $value) ? $value : null;
    }
    
    /**
     * @return array
     */
    public function getMessage() {
        return ['flags'];
    }
}
