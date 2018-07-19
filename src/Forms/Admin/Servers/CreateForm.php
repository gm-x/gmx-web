<?php
namespace GameX\Forms\Admin\Servers;

use \Firebase\JWT\JWT;

class CreateForm extends Form {

	/**
	 * @var string
	 */
	protected $secret;

	/**
	 * @param string $secret
	 * @return $this
	 */
	public function setSecret($secret) {
		$this->secret = (string) $secret;
		return $this;
	}

	/**
	 * @return boolean
	 */
	protected function processForm() {
		$this->server->fill($this->form->getValues());
		$this->server->token = JWT::encode([
			'server_id' => $this->server->id
		], $this->secret, 'HS512');
		$this->server->save();
		return true;
	}
}
