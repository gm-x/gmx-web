<?php
use \GameX\Controllers\Admin\PreferencesController;
use \GameX\Core\Auth\Permissions;
use \GameX\Constants\Admin\PreferencesConstants;

return function () {
	/** @var \Slim\App $this */
    
    /** @var Permissions $permissions */
    $permissions = $this->getContainer()->get('permissions');

    $this
        ->get('', [PreferencesController::class, 'main'])
        ->setName(PreferencesConstants::ROUTE_MAIN)
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_MAIN_KEY,
            Permissions::ACCESS_VIEW
        ));

    $this
        ->post('', [PreferencesController::class, 'main'])
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_MAIN_KEY,
            Permissions::ACCESS_EDIT
        ));

	$this
		->get('/email', [PreferencesController::class, 'email'])
		->setName(PreferencesConstants::ROUTE_EMAIL)
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_EMAIL_KEY,
            Permissions::ACCESS_VIEW
        ));
    
    $this
        ->post('/email', [PreferencesController::class, 'email'])
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_EMAIL_KEY,
            Permissions::ACCESS_EDIT
        ));

	$this
		->post('/email/test', [PreferencesController::class, 'test'])
		->setName(PreferencesConstants::ROUTE_EMAIL_TEST)
		->setArgument('csrf_skip', true)
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_EMAIL_KEY,
            Permissions::ACCESS_VIEW | Permissions::ACCESS_EDIT
        ));

	$this
		->get('/update', [PreferencesController::class, 'update'])
		->setName(PreferencesConstants::ROUTE_UPDATE)
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_UPDATE_KEY,
            Permissions::ACCESS_VIEW
        ));
    
    $this
        ->post('/update', [PreferencesController::class, 'update'])
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_UPDATE_KEY,
            Permissions::ACCESS_EDIT
        ));
    
    $this
        ->get('/cache', [PreferencesController::class, 'cache'])
        ->setName(PreferencesConstants::ROUTE_CACHE)
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_CACHE_KEY,
            Permissions::ACCESS_VIEW
        ));
    
    $this
        ->post('/cache', [PreferencesController::class, 'cache'])
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_CACHE_KEY,
            Permissions::ACCESS_EDIT
        ));
    
    $this
        ->get('/cron', [PreferencesController::class, 'cron'])
        ->setName(PreferencesConstants::ROUTE_CRON)
        ->add($permissions->hasAccessToPermissionMiddleware(
            PreferencesConstants::PERMISSION_GROUP,
            PreferencesConstants::PERMISSION_CRON_KEY,
            Permissions::ACCESS_VIEW
        ));

	$this
		->get('/social', [PreferencesController::class, 'social'])
		->setName(PreferencesConstants::ROUTE_SOCIAL)
		->add($permissions->hasAccessToPermissionMiddleware(
			PreferencesConstants::PERMISSION_GROUP,
			PreferencesConstants::PERMISSION_CACHE_KEY,
			Permissions::ACCESS_VIEW
		));

	$this
		->post('/social', [PreferencesController::class, 'social'])
		->add($permissions->hasAccessToPermissionMiddleware(
			PreferencesConstants::PERMISSION_GROUP,
			PreferencesConstants::PERMISSION_SOCIAL_KEY,
			Permissions::ACCESS_EDIT
		));
};
