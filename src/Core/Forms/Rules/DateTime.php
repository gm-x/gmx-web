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
     * @inheritdoc
     */
    public function validate($value, array $values) {
        if ($value instanceof DateTimeInput) {
            return $value;
        } else {
            $date = date_parse_from_format($this->format, $value);
            return $date['error_count'] === 0 ? $date : null;
        }
    }
    
    /**
     * @return array
     */
	protected function getMessage() {
        return ['date_time'];
    }
}
