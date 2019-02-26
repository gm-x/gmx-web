<?php
namespace GameX\Core\Validate\Rules;

use \GameX\Core\Validate\Rule;
use \GameX\Core\Lang\Language;

class Callback implements Rule {
    
    /**
     * @var callable
     */
    protected $callback;
    
    /**
     * @var string
     */
    protected $message;
    
    /**
     * @param callable $callback
     * @param string $message
     */
    public function __construct(callable $callback, $message = '') {
        $this->callback = $callback;
        $this->message = (string) $message;
    }
    
    /**
     * @inheritdoc
     */
    public function validate($value, array $values) {
        return call_user_func($this->callback, $value, $values);
    }
    
    /**
     * @param Language $language
     * @return string
     */
    public function getError(Language $language) {
        return $this->message;
    }
}
