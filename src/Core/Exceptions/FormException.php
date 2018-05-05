<?php

namespace GameX\Core\Exceptions;

use \Exception;

class FormException extends Exception {
    /**
     * @var string
     */
    protected $field;

    /**
     * FormException constructor.
     * @param string $field
     * @param string $message
     * @param int $code
     */
    public function __construct($field, $message = "", $code = 0) {
        $this->field = $field;
        parent::__construct($message, $code);
    }

    /**
     * @return string
     */
    public function getField() {
        return $this->field;
    }
}
