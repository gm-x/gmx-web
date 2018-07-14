<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\Admin\PreferencesController;

return function () {
	/** @var \Slim\App $this */
	$this
        ->map(['GET', 'POST'], '', BaseController::action(PreferencesController::class, 'index'))
		->setName('admin_preferences_index')
		->setArgument('permission', 'admin.preferences');

	$this
		->map(['GET', 'POST'], '/email', BaseController::action(PreferencesController::class, 'email'))
		->setName('admin_preferences_email')
		->setArgument('permission', 'admin.preferences');

	$this
		->post('/email/test', BaseController::action(PreferencesController::class, 'test'))
		->setName('admin_preferences_email_test')
		->setArgument('permission', 'admin.preferences')
		->setArgument('csrf_skip', true);
};
