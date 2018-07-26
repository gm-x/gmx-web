<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Form;
use \GameX\Core\Forms\Element;
use \GameX\Core\Forms\Elements\DateTimeInput;

class DateTime extends BaseRule {
    
    /**
     * @var string
     */
    protected $format = 'Y-m-d H:i:s';
    
    /**
     * @param Form $form
     * @param Element $element
     * @return bool
     */
    protected function isValid(Form $form, Element $element) {
        if ($element instanceof DateTimeInput) {
            return $element->getValue() !== null;
        } else {
            $date = date_parse_from_format($this->format, $element->getValue());
            return $date['error_count'] === 0;
        }
    }
    
    /**
     * @return array
     */
	protected function getMessage() {
        return ['date_time'];
    }
}
