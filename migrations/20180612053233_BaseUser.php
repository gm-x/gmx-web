<?php

use \GameX\Core\Migration;
use \GameX\Core\Auth\Helpers\AuthHelper;

class BaseUser extends Migration {

	/**
	 * Do the migration
	 */
	public function up() {
		$authHelper = new AuthHelper($this->container);
		$activationCode = $authHelper->registerUser(
			'admin@example.com',
			'test123',
			'test123'
		);
	}

	/**
	 * Undo the migration
	 */
	public function down() {}
}
