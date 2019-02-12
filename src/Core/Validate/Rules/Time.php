<?php
namespace GameX\Core\Validate\Rules;

use \GameX\Core\Validate\Rules\DateTime as DateTimeRule;

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
