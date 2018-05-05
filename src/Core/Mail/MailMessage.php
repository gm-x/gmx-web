<?php
namespace GameX\Core\Mail;

use \Tx\Mailer\Message;

class MailMessage extends Message {
    public function getBodyForMail() {
        return empty($this->attachment)
            ? $this->createBody()
            : $this->createBodyWithAttachment();
    }

    public function getHeadersForMail() {
        $headers = '';
        $this->createHeader();
        foreach ($this->header as $key => $value) {
            $headers .= $key . ': ' . $value . $this->CRLF;
        }

        return $headers;
    }
}
