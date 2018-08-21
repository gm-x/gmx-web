<?php
namespace GameX\Core\Forms\Rules;

class Boolean extends BaseRule {
    
    /**
     * @inheritdoc
     */
    public function validate($value, array $values) {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
    
    /**
     * @return array
     */
	protected function getMessage() {
        return ['boolean'];
    }
}
