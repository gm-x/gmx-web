<?php
namespace GameX\Core\Mail;

abstract class Sender {
	/**
	 * @var Formatter
	 */
    protected $formatter;

    public function __construct() {
    	$this->formatter = new Formatter();
    }

    abstract public function send(Message $message);
}
