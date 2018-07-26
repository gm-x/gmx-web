<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;

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
        $this->min = (int) $min;
        $this->max = (int) $max;
    }
    
    /**
     * @param Form $form
     * @param Element $element
     * @return bool
     */
    protected function isValid(Form $form, Element $element) {
        $len = mb_strlen($element->getValue());

        if ($this->min !== null && $len < $this->min) {
            return false;
        }
    
        if ($this->max !== null && $len > $this->max) {
            return false;
        }
        
        return true;
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
