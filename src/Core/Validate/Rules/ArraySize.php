<?php
namespace GameX\Core\Validate\Rules;

class ArraySize extends BaseRule {
    
    /**
     * @var integer|null
     */
    protected $min;
    
    /**
     * @var integer|null
     */
    protected $max;
    
    /**
     * @param integer|null $min
     * @param integer|null $max
     */
    public function __construct($min = null, $max = null) {
        $this->min = $min;
        $this->max = $max;
    }
    
    /**
     * @inheritdoc
     */
    public function validate($value, array $values) {
    	if (!is_array($value)) {
    		return null;
	    }

        $count = count($value);

        if ($this->min !== null && $count < $this->min) {
            return null;
        }
    
        if ($this->max !== null && $count > $this->max) {
            return null;
        }
        
        return $value;
    }
    
    /**
     * @return array|null
     */
    public function getMessage() {
        if ($this->min !== null && $this->max !== null) {
            return ['array_min_max', [$this->min, $this->max]];
        } elseif ($this->min !== null) {
            return ['array_min', [$this->min]];
        } elseif ($this->max !== null) {
            return ['array_max', [$this->max]];
        } else {
            return ['array'];
        }
    }
}
