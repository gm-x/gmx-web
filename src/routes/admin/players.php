<?php
use \GameX\Core\BaseController;
use \GameX\Constants\Admin\PlayersConstants;
use \GameX\Constants\Admin\PrivilegesConstants;
use \GameX\Constants\Admin\PunishmentsConstants;
use \GameX\Controllers\Admin\PlayersController;
use \GameX\Controllers\Admin\PrivilegesController;
use \GameX\Controllers\Admin\PunishmentsController;
use \GameX\Core\Auth\Permissions;

return function () {
    /** @var \Slim\App $this */

    /** @var Permissions $permissions */
    $permissions = $this->getContainer()->get('permissions');

    $this
        ->get('', BaseController::action(PlayersController::class, 'index'))
        ->setName(PlayersConstants::ROUTE_LIST)
        ->add($permissions->hasAccessToPermissionMiddleware(
            PlayersConstants::PERMISSION_GROUP,
            PlayersConstants::PERMISSION_KEY,
            Permissions::ACCESS_LIST
        ));

	$this
		->map(['GET', 'POST'], '/create', BaseController::action(PlayersController::class, 'create'))
		->setName(PlayersConstants::ROUTE_CREATE)
        ->add($permissions->hasAccessToPermissionMiddleware(
            PlayersConstants::PERMISSION_GROUP,
            PlayersConstants::PERMISSION_KEY,
            Permissions::ACCESS_CREATE
        ));

    $this
        ->map(['GET', 'POST'], '/{player}/edit', BaseController::action(PlayersController::class, 'edit'))
        ->setName(PlayersConstants::ROUTE_EDIT)
        ->add($permissions->hasAccessToPermissionMiddleware(
            PlayersConstants::PERMISSION_GROUP,
            PlayersConstants::PERMISSION_KEY,
            Permissions::ACCESS_EDIT
        ));

	$this
		->post('/{player}/delete', BaseController::action(PlayersController::class, 'delete'))
		->setName(PlayersConstants::ROUTE_DELETE)
        ->add($permissions->hasAccessToPermissionMiddleware(
            PlayersConstants::PERMISSION_GROUP,
            PlayersConstants::PERMISSION_KEY,
            Permissions::ACCESS_DELETE
        ));

    $this->group('/{player}/privileges', function () {
        /** @var \Slim\App $this */

        /** @var Permissions $permissions */
        $permissions = $this->getContainer()->get('permissions');

        $this
            ->get('', BaseController::action(PrivilegesController::class, 'index'))
            ->setName(PrivilegesConstants::ROUTE_LIST);

        $this
            ->map(['GET', 'POST'], '/create/{server}', BaseController::action(PrivilegesController::class, 'create'))
            ->setName(PrivilegesConstants::ROUTE_CREATE)
            ->add($permissions->hasAccessToResourceMiddleware(
                'server',
                PrivilegesConstants::PERMISSION_GROUP,
                PrivilegesConstants::PERMISSION_KEY,
                Permissions::ACCESS_CREATE
            ));

        $this
            ->map(['GET', 'POST'], '/{privilege}/edit', BaseController::action(PrivilegesController::class, 'edit'))
            ->setName(PrivilegesConstants::ROUTE_EDIT);

        $this
            ->post('/{privilege}/delete', BaseController::action(PrivilegesController::class, 'delete'))
            ->setName(PrivilegesConstants::ROUTE_DELETE);
    });
    
    $this->group('/{player}/punishments', function () {
        /** @var \Slim\App $this */
        
        /** @var Permissions $permissions */
        $permissions = $this->getContainer()->get('permissions');
        
        $this
            ->get('', BaseController::action(PunishmentsController::class, 'index'))
            ->setName(PunishmentsConstants::ROUTE_LIST);
    
        $this
            ->map(['GET', 'POST'], '/create/{server}', BaseController::action(PunishmentsController::class, 'create'))
            ->setName(PunishmentsConstants::ROUTE_CREATE)
            ->add($permissions->hasAccessToResourceMiddleware(
                'server',
                PunishmentsConstants::PERMISSION_GROUP,
                PunishmentsConstants::PERMISSION_KEY,
                Permissions::ACCESS_CREATE
            ));
    
        $this
            ->map(['GET', 'POST'], '/{punishment}/edit', BaseController::action(PunishmentsController::class, 'edit'))
            ->setName(PunishmentsConstants::ROUTE_EDIT)
            ->add($permissions->hasAccessToResourceMiddleware(
                'server',
                PunishmentsConstants::PERMISSION_GROUP,
                PunishmentsConstants::PERMISSION_KEY,
                Permissions::ACCESS_CREATE
            ));
    });
};
