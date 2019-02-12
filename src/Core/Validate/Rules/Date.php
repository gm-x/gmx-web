<?php
namespace GameX\Core\Validate\Rules;

use \GameX\Core\Validate\Rules\DateTime as DateTimeRule;

class Date extends DateTimeRule {
    
    /**
     * @var string
     */
    protected $format = 'Y-m-d';

    /**
     * @return array
     */
	protected function getMessage() {
        return ['date'];
    }
}
