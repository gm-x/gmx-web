<?php
namespace GameX\Core\Mail\Senders;

use \GameX\Core\Mail\Sender;
use \GameX\Core\Mail\Message;
use \GameX\Core\Mail\Exceptions\SendException;

class Mail extends Sender {
	public function send(Message $message) {
		if (!mail(
			$this->formatter->getFromMail($message),
			$this->formatter->getSubject($message),
			$this->formatter->getBody($message),
			$this->formatter->getHeaders($message)
		)) {
			throw new SendException();
		}
	}
}
