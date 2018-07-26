<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Rules\DateTime as DateTimeRule;

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
