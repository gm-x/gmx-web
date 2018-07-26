<?php

namespace GameX\Core\Mail;

class Email {

	/**
	 * @var string
	 */
	protected $email;

	/**
	 * @var string|null
	 */
	protected $name;

	/**
	 * Email constructor.
	 * @param string $email
	 * @param string|null $name
	 */
	public function __construct($email, $name = null) {
		$this->email = (string) $email;
		$this->name = $name !== null ? (string) $name : null;
	}

	/**
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @return null|string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->name === null
			? $this->email
			: sprintf('=?utf-8?B?%s?= <%s>', base64_encode($this->name), $this->email);
	}
}
