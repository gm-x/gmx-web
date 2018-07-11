<?php
namespace GameX\Core\Mail\Senders;

use \GameX\Core\Mail\Sender;
use \GameX\Core\Mail\Message;
use \GameX\Core\Mail\Exceptions\SendException;

class Mail implements Sender {
	public function send(Message $message) {
		$headers = $message->getHeaders();
		$header = '';
		foreach ($headers as $key => $value) {
			$header .= $key . ': ' . $value;
		}
		if (!mail((string) $message->getFrom(), $message->getSubject(), $message->getMessage(), $header)) {
			throw new SendException();
		}
	}
}
