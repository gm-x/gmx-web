<?php
namespace GameX\Core\Exceptions;

use \Exception;

class RedirectException extends Exception {
	protected $url;
	protected $status;

	public function __construct($url, $status = 301) {
		parent::__construct('', 0, null);
		$this->url = $url;
		$this->status = $status;
	}

	public function getUrl() {
		return $this->url;
	}
	
	public function getStatus() {
	    return $this->status;
    }
}
