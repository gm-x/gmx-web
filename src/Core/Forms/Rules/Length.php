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
     * @return array
     */
    public function getMessageKey() {
        if ($this->min !== null && $this->max !== null) {
            return ['min_max_length', [$this->min, $this->max]];
        } elseif ($this->min !== null) {
            return ['min_length', [$this->min]];
        } elseif ($this->max !== null) {
            return ['max_length', [$this->max]];
        } else {
            return [];
        }
    }
}
