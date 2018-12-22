<?php
namespace GameX\Core\Mail;

interface Sender {

    /**
     * @param Message $message
     */
    public function send(Message $message);
}
