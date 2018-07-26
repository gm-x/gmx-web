<?php
namespace GameX\Core\Mail\Exceptions;

class CodeException extends MailException {
    public function __construct($expected, $received, $serverMessage = null) {
        $message = "Unexpected return code - Expected: {$expected}, Got: {$received}";
        if ($serverMessage !== null) {
            $message .= " | " . $serverMessage;
        }
        parent::__construct($message);
    }
}
