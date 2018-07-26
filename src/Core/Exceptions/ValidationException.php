<?php
namespace GameX\Core\Exceptions;

use \Exception;

class ValidationException extends Exception {
	public function hasMessage() {
		return !empty($this->message);
	}
}
