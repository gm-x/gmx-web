<?php

use \GameX\Core\Migration;
use \Pimple\Psr11\Container as PsrContainer;

class BaseUser extends Migration {

	/**
	 * Do the migration
	 */
	public function up() {
        $container = new PsrContainer($this->container);
	    \GameX\Core\BaseModel::setContainer($container);
	    /** @var \Cartalyst\Sentinel\Sentinel $auth */
	    $auth = $container->get('auth');
        $auth->register([
            'email'  => 'admin@example.com',
            'password' => 'test123',
        ], true);
	}

	/**
	 * Undo the migration
	 */
	public function down() {}
}
