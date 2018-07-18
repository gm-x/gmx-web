<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;
use \GameX\Core\Forms\BadOptionException;

class Length extends BaseRule {
    protected function isValid(Form $form, Element $element) {
        $min = null;
        $max = null;
        $len = mb_strlen($element->getValue());
        
        try {
            $min = $this->getOption('min');
        } catch (BadOptionException $e) {
            $min = null;
        }
        
        if ($min !== null && $len < $min) {
            return false;
        }
    
        try {
            $max = $this->getOption('max');
        } catch (BadOptionException $e) {
            $max = null;
        }
    
        if ($max !== null && $len > $max) {
            return false;
        }
        
        return true;
    }
    
    public function getMessageKey() {
        $min = null;
        $max = null;
    
        try {
            $min = $this->getOption('min');
        } catch (BadOptionException $e) {
            $min = null;
        }
    
        try {
            $max = $this->getOption('max');
        } catch (BadOptionException $e) {
            $max = null;
        }
        
        if ($min !== null && $max !== null) {
            return ['min_max_length', [$min, $max]];
        } elseif ($min !== null) {
            return ['min_length', [$min]];
        } elseif ($max !== null) {
            return ['max_length', [$max]];
        } else {
            return [];
        }
    }
}
