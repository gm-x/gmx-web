<?php
namespace GameX\Core\Validate\Rules;

class ArrayRule extends BaseRule {
    
    /**
     * @inheritdoc
     */
    public function validate($value, array $values) {
    	if (!is_array($value)) {
    		return null;
	    }
        
        return $value;
    }
    
    /**
     * @return array|null
     */
    public function getMessage() {
        return ['array'];
    }
}
