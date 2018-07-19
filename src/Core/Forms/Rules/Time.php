<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Rules\DateTime as DateTimeRule;

class Time extends DateTimeRule {
    
    /**
     * @var string
     */
    protected $format = 'H:i:s';

    /**
     * @return array
     */
	protected function getMessage() {
        return ['time'];
    }
}
