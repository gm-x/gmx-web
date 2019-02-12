<?php
namespace GameX\Core\Validate\Rules;

use \GameX\Core\Validate\Rule;
use \GameX\Core\Lang\Language;

abstract class BaseRule implements Rule {

    /**
     * @param Language $language
     * @return string
     */
    public function getError(Language $language) {
        $message = $this->getMessage();
        if ($message === null) {
        	return '';
		}
		$key = isset($message[0]) ? $message[0]: null;
		$args = isset($message[1]) ? $message[1]: null;
        return $language->format($this->getErrorSection(), $key, $args);
    }
    
    /**
     * @return string
     */
    protected function getErrorSection() {
        return 'forms';
    }
    
    /**
     * @return array|null
     */
    protected function getMessage() {
        return null;
    }
}
