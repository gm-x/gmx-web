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
        ->get('', BaseController::action(PreferencesController::class, 'main'))
        ->setName(PreferencesConstants::ROUTE_MAIN)
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_MAIN_KEY,
            Permissions::ACCESS_VIEW
        ));

    $this
        ->post('', BaseController::action(PreferencesController::class, 'main'))
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_MAIN_KEY,
            Permissions::ACCESS_EDIT
        ));

	$this
		->get('/email', BaseController::action(PreferencesController::class, 'email'))
		->setName(PreferencesConstants::ROUTE_EMAIL)
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_EMAIL_KEY,
            Permissions::ACCESS_VIEW
        ));
    
    $this
        ->post('/email', BaseController::action(PreferencesController::class, 'email'))
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_EMAIL_KEY,
            Permissions::ACCESS_EDIT
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

	$this
		->get('/update', BaseController::action(PreferencesController::class, 'update'))
		->setName(PreferencesConstants::ROUTE_UPDATE)
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_UPDATE_KEY,
            Permissions::ACCESS_VIEW
        ));
    
    $this
        ->post('/update', BaseController::action(PreferencesController::class, 'update'))
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_UPDATE_KEY,
            Permissions::ACCESS_EDIT
        ));
    
    $this
        ->get('/cache', BaseController::action(PreferencesController::class, 'cache'))
        ->setName(PreferencesConstants::ROUTE_CACHE)
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_CACHE_KEY,
            Permissions::ACCESS_VIEW
        ));
    
    $this
        ->post('/cache', BaseController::action(PreferencesController::class, 'cache'))
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_CACHE_KEY,
            Permissions::ACCESS_EDIT
        ));
    
    $this
        ->get('/cron', BaseController::action(PreferencesController::class, 'cron'))
        ->setName(PreferencesConstants::ROUTE_CRON)
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_CRON_KEY,
            Permissions::ACCESS_VIEW
        ));
    
    $this
        ->post('/cron', BaseController::action(PreferencesController::class, 'cron'))
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_CRON_KEY,
            Permissions::ACCESS_EDIT
        ));
};
