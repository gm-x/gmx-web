<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\Admin\PreferencesController;
use \GameX\Core\Auth\Permissions;

return function () {
	/** @var \Slim\App $this */

    /** @var Permissions $permissions */
    $permissions = $this->getContainer()->get('permissions');

	$this
        ->map(['GET', 'POST'], '', BaseController::action(PreferencesController::class, 'index'))
		->setName('admin_preferences_index')
		->add($permissions->hasAccessToPermissionMiddleware('admin', 'preferences', Permissions::ACCESS_EDIT));

	$this
		->map(['GET', 'POST'], '/email', BaseController::action(PreferencesController::class, 'email'))
		->setName('admin_preferences_email')
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'preferences', Permissions::ACCESS_EDIT));

	$this
		->post('/email/test', BaseController::action(PreferencesController::class, 'test'))
		->setName('admin_preferences_email_test')
		->setArgument('csrf_skip', true)
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'preferences', Permissions::ACCESS_EDIT));
};
