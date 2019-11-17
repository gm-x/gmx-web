<?php
namespace GameX\Core\Validate\Rules;

class ArrayBoolean extends BaseRule {
    
    /**
     * @inheritdoc
     */
    public function validate($value, array $values) {
    	if (!is_array($value)) {
    		return null;
	    }

        $value = array_map(function ($val) {
            return filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }, $value);


    	return array_filter($value, function ($val) {
    	    return $val !== null;
        });
    }
}
