<?php
namespace GameX\Core\Forms\Rules;

use \GameX\Core\Forms\Rule;
use \GameX\Core\Forms\Form;
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
     * @param Form $form
     * @param string $key
     * @return bool
     */
    public function validate(Form $form, $key) {
        return call_user_func($this->callback, $form, $key);
    }
    
    /**
     * @param Language $language
     * @return string
     */
    public function getError(Language $language) {
        return $this->message;
    }
}
