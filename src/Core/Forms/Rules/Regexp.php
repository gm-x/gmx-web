<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;

class Regexp extends BaseRule {
    
    /**
     * @var string
     */
    protected $regexp;
    
    /**
     * @param string $regexp
     */
    public function __construct($regexp) {
        $this->regexp = (string) $regexp;
    }
    
    /**
     * @param Form $form
     * @param Element $element
     * @return bool
     */
    protected function isValid(Form $form, Element $element) {
        return (bool) preg_match($this->regexp, $element->getValue());
    }
    
    /**
     * @return array
     */
    public function getMessage() {
        return ['regexp'];
    }
}
