<?php
namespace GameX\Core\Forms\Rules;

class Required extends BaseRule {
    
    /**
     * @inheritdoc
     */
    public function validate($value, array $values) {
        return $value !== null && !empty($value) ? $value : null;
    }
    
    /**
     * @return array
     */
    public function getMessage() {
        return ['required'];
    }
}
