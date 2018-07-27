<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Rule;
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
		list ($key, $args) = $message;
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
