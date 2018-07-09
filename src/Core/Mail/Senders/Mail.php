<?php
namespace GameX\Core\Mail\Senders;

use \GameX\Core\Mail\Sender;
use \GameX\Core\Mail\Message;
use \GameX\Core\Mail\Exceptions\SendException;

class Mail implements Sender {
	public function send(Message $message) {
		if (!mail(
			(string) $message->getFrom(),
			$message->getSubject(),
			$message->getMessage(),
			$message->getHeaders()
		)) {
			throw new SendException();
		}
	}
}
