<?php
namespace GameX\Core\Forms\Rules;

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
