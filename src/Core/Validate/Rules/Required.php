<?php
namespace GameX\Core\Validate\Rules;

class Required extends BaseRule {
    
    /**
     * @inheritdoc
     */
    public function validate($value, array $values) {
        return $value !== null && strlen($value) > 0 ? $value : null;
    }
    
    /**
     * @return array
     */
    public function getMessage() {
        return ['required'];
    }
}
