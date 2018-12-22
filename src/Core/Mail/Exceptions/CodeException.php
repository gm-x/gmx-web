<?php
namespace GameX\Core\Mail\Exceptions;

class CodeException extends MailException {

    /**
     * @var int
     */
    protected $expected;

    /**
     * @var int
     */
    protected $received;

    /**
     * @param int $expected
     * @param int $received
     * @param string|null $serverMessage
     */
    public function __construct($expected, $received, $serverMessage = null) {
        $message = "Unexpected return code - Expected: {$expected}, Got: {$received}";
        if ($serverMessage !== null) {
            $message .= " | " . $serverMessage;
        }
        parent::__construct($message);
    }

    /**
     * @return int
     */
    public function getExpected() {
        return $this->expected;
    }

    /**
     * @return int
     */
    public function getReceived() {
        return $this->received;
    }
}
