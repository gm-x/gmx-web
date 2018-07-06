<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\Admin\PreferencesController;

return function () {
	/** @var \Slim\App $this */
	$this
		->get('', BaseController::action(PreferencesController::class, 'index'))
		->setName('admin_preferences')
		->setArgument('permission', 'admin.preferences');
};
