<?php
namespace GameX\Core\Exceptions;

use \Exception;

class RedirectException extends Exception {
	protected $url;

	public function __construct($url, $code = 0) {
		parent::__construct('', $code, null);
		$this->url = $url;
	}

	public function getUrl() {
		return $this->url;
	}
}
