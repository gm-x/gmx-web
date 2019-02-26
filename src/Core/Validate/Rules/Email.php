<?php
namespace GameX\Core\Validate\Rules;

class Email extends BaseRule {
    
    /**
     * @inheritdoc
     */
    public function validate($value, array $values) {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false ? $value : null;
    }
    
    /**
     * @return array
     */
	protected function getMessage() {
        return ['email'];
    }
}
