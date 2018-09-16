<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\Admin\PreferencesController;
use \GameX\Core\Auth\Permissions;
use \GameX\Constants\Admin\PreferencesConstants;

return function () {
	/** @var \Slim\App $this */

    /** @var Permissions $permissions */
    $permissions = $this->getContainer()->get('permissions');

	$this
        ->map(['GET', 'POST'], '', BaseController::action(PreferencesController::class, 'index'))
		->setName(PreferencesConstants::ROUTE_MAIN)
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_MAIN_KEY,
            Permissions::ACCESS_VIEW | Permissions::ACCESS_EDIT
        ));

	$this
		->map(['GET', 'POST'], '/email', BaseController::action(PreferencesController::class, 'email'))
		->setName(PreferencesConstants::ROUTE_EMAIL)
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_EMAIL_KEY,
            Permissions::ACCESS_VIEW | Permissions::ACCESS_EDIT
        ));

	$this
		->post('/email/test', BaseController::action(PreferencesController::class, 'test'))
		->setName(PreferencesConstants::ROUTE_EMAIL_TEST)
		->setArgument('csrf_skip', true)
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_EMAIL_KEY,
            Permissions::ACCESS_VIEW | Permissions::ACCESS_EDIT
        ));
};
