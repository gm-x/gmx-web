<?php
use \GameX\Constants\Admin\ServersConstants;
use \GameX\Constants\Admin\GroupsConstants;
use \GameX\Constants\Admin\ReasonsConstants;
use \GameX\Core\Auth\Permissions;
use \GameX\Controllers\Admin\ServersController;
use \GameX\Controllers\Admin\GroupsController;
use \GameX\Controllers\Admin\ReasonsController;

return function () {
    /** @var \Slim\App $this */

    /** @var Permissions $permissions */
    $permissions = $this->getContainer()->get('permissions');

    $this
        ->get('', [ServersController::class, 'index'])
        ->setName(ServersConstants::ROUTE_LIST)
        ->add($permissions->hasAccessToPermissionMiddleware(
            ServersConstants::PERMISSION_GROUP,
            ServersConstants::PERMISSION_KEY,
            Permissions::ACCESS_LIST
        ));
    
    $this
        ->get('/{server:\d+}/token', [ServersController::class, 'token'])
        ->setName(ServersConstants::ROUTE_TOKEN)
        ->add($permissions->hasAccessToResourceMiddleware(
            'server',
            ServersConstants::PERMISSION_TOKEN_GROUP,
            ServersConstants::PERMISSION_TOKEN_KEY,
            Permissions::ACCESS_CREATE | Permissions::ACCESS_CREATE
        ));

    $this
        ->get('/{server:\d+}/view', [ServersController::class, 'view'])
        ->setName(ServersConstants::ROUTE_VIEW)
        ->add($permissions->hasAccessToPermissionMiddleware(
            ServersConstants::PERMISSION_GROUP,
            ServersConstants::PERMISSION_KEY,
            Permissions::ACCESS_VIEW
        ));

    $this
        ->map(['GET', 'POST'], '/create', [ServersController::class, 'create'])
        ->setName(ServersConstants::ROUTE_CREATE)
        ->add($permissions->hasAccessToPermissionMiddleware(
            ServersConstants::PERMISSION_GROUP,
            ServersConstants::PERMISSION_KEY,
            Permissions::ACCESS_CREATE
        ));

    $this
        ->map(['GET', 'POST'], '/{server:\d+}/edit', [ServersController::class, 'edit'])
        ->setName(ServersConstants::ROUTE_EDIT)
        ->add($permissions->hasAccessToPermissionMiddleware(
            ServersConstants::PERMISSION_GROUP,
            ServersConstants::PERMISSION_KEY,
            Permissions::ACCESS_EDIT
        ));

    $this
        ->post('/{server:\d+}/delete', [ServersController::class, 'delete'])
        ->setName(ServersConstants::ROUTE_DELETE)
        ->add($permissions->hasAccessToPermissionMiddleware(
            ServersConstants::PERMISSION_GROUP,
            ServersConstants::PERMISSION_KEY,
            Permissions::ACCESS_DELETE
        ));

    $this->group('/{server:\d+}/groups', function () {
        /** @var \Slim\App $this */

        /** @var Permissions $permissions */
        $permissions = $this->getContainer()->get('permissions');

        $this
            ->get('', [GroupsController::class, 'index'])
            ->setName(GroupsConstants::ROUTE_LIST)
            ->add($permissions->hasAccessToResourceMiddleware(
                'server',
                GroupsConstants::PERMISSION_GROUP,
                GroupsConstants::PERMISSION_KEY,
                Permissions::ACCESS_LIST
            ));

        $this
            ->map(['GET', 'POST'], '/create', [GroupsController::class, 'create'])
            ->setName(GroupsConstants::ROUTE_CREATE)
            ->add($permissions->hasAccessToResourceMiddleware(
                'server',
                GroupsConstants::PERMISSION_GROUP,
                GroupsConstants::PERMISSION_KEY,
                Permissions::ACCESS_CREATE
            ));

        $this
            ->map(['GET', 'POST'], '/{group:\d+}/edit', [GroupsController::class, 'edit'])
            ->setName(GroupsConstants::ROUTE_EDIT)
            ->add($permissions->hasAccessToResourceMiddleware(
                'server',
                GroupsConstants::PERMISSION_GROUP,
                GroupsConstants::PERMISSION_KEY,
                Permissions::ACCESS_EDIT
            ));

        $this
            ->post('/{group:\d+}/delete', [GroupsController::class, 'delete'])
            ->setName(GroupsConstants::ROUTE_DELETE)
            ->add($permissions->hasAccessToResourceMiddleware(
                'server',
                GroupsConstants::PERMISSION_GROUP,
                GroupsConstants::PERMISSION_KEY,
                Permissions::ACCESS_DELETE
            ));
    
        $this
            ->post('/priority', [GroupsController::class, 'priority'])
            ->setName(GroupsConstants::ROUTE_PRIORITY)
            ->add($permissions->hasAccessToResourceMiddleware(
                'server',
                GroupsConstants::PERMISSION_GROUP,
                GroupsConstants::PERMISSION_KEY,
                Permissions::ACCESS_EDIT
            ));
    });
    
    $this->group('/{server:\d+}/reasons', function () {
        /** @var \Slim\App $this */

        /** @var Permissions $permissions */
        $permissions = $this->getContainer()->get('permissions');

        $this
            ->get('', [ReasonsController::class, 'index'])
            ->setName(ReasonsConstants::ROUTE_LIST)
            ->add($permissions->hasAccessToResourceMiddleware(
                'server',
                ReasonsConstants::PERMISSION_GROUP,
                ReasonsConstants::PERMISSION_KEY,
                Permissions::ACCESS_LIST
            ));
        
        $this
            ->map(['GET', 'POST'], '/create', [ReasonsController::class, 'create'])
            ->setName(ReasonsConstants::ROUTE_CREATE)
            ->add($permissions->hasAccessToResourceMiddleware(
                'server',
                ReasonsConstants::PERMISSION_GROUP,
                ReasonsConstants::PERMISSION_KEY,
                Permissions::ACCESS_CREATE
            ));
        
        $this
            ->map(['GET', 'POST'], '/{reason:\d+}/edit', [ReasonsController::class, 'edit'])
            ->setName(ReasonsConstants::ROUTE_EDIT)
            ->add($permissions->hasAccessToResourceMiddleware(
                'server',
                ReasonsConstants::PERMISSION_GROUP,
                ReasonsConstants::PERMISSION_KEY,
                Permissions::ACCESS_EDIT
            ));
        
        $this
            ->post('/{reason:\d+}/delete', [ReasonsController::class, 'delete'])
            ->setName(ReasonsConstants::ROUTE_DELETE)
            ->add($permissions->hasAccessToResourceMiddleware(
                'server',
                ReasonsConstants::PERMISSION_GROUP,
                ReasonsConstants::PERMISSION_KEY,
                Permissions::ACCESS_DELETE
            ));
    });
};
