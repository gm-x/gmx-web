<?php
namespace GameX\Core\Validate\Rules;

class Length extends BaseRule {
    
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
        $len = mb_strlen($value);

        if ($this->min !== null && $len < $this->min) {
            return null;
        }
    
        if ($this->max !== null && $len > $this->max) {
            return null;
        }
        
        return $value;
    }
    
    /**
     * @return array|null
     */
    public function getMessage() {
        if ($this->min !== null && $this->max !== null) {
            return ['length_min_max', [$this->min, $this->max]];
        } elseif ($this->min !== null) {
            return ['length_min', [$this->min]];
        } elseif ($this->max !== null) {
            return ['length_max', [$this->max]];
        } else {
            return null;
        }
    }
}
