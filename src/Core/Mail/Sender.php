<?php
namespace GameX\Core\Mail;

interface Sender {
    public function send(Message $message);
}
