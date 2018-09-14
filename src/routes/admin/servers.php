<?php
use \GameX\Core\BaseController;
use \GameX\Constants\Admin\ServersConstants;
use \GameX\Core\Auth\Permissions;
use \GameX\Controllers\Admin\ServersController;
use \GameX\Controllers\Admin\GroupsController;
use \GameX\Controllers\Admin\ReasonsController;

return function () {
    /** @var \Slim\App $this */

    /** @var Permissions $permissions */
    $permissions = $this->getContainer()->get('permissions');

    $this
        ->get('', BaseController::action(ServersController::class, 'index'))
        ->setName(ServersConstants::ROUTE_LIST)
        ->add($permissions->hasAccessToPermissionMiddleware(
            ServersConstants::PERMISSIONS_GROUP,
            ServersConstants::PERMISSION_SERVER,
            Permissions::ACCESS_LIST
        ));
    
    $this
        ->get('/{server}/token', BaseController::action(ServersController::class, 'token'))
        ->setName(ServersConstants::ROUTE_TOKEN)
        ->add($permissions->hasAccessToResourceMiddleware(
            'server',
            ServersConstants::PERMISSIONS_GROUP,
            ServersConstants::PERMISSION_TOKEN,
            Permissions::ACCESS_CREATE | Permissions::ACCESS_CREATE
        ));

    $this
        ->get('/{server}/view', BaseController::action(ServersController::class, 'view'))
        ->setName(ServersConstants::ROUTE_VIEW)
        ->add($permissions->hasAccessToPermissionMiddleware(
            ServersConstants::PERMISSIONS_GROUP,
            ServersConstants::PERMISSION_SERVER,
            Permissions::ACCESS_VIEW
        ));

    $this
        ->map(['GET', 'POST'], '/create', BaseController::action(ServersController::class, 'create'))
        ->setName(ServersConstants::ROUTE_CREATE)
        ->add($permissions->hasAccessToPermissionMiddleware(
            ServersConstants::PERMISSIONS_GROUP,
            ServersConstants::PERMISSION_SERVER,
            Permissions::ACCESS_CREATE
        ));

    $this
        ->map(['GET', 'POST'], '/{server}/edit', BaseController::action(ServersController::class, 'edit'))
        ->setName(ServersConstants::ROUTE_EDIT)
        ->add($permissions->hasAccessToPermissionMiddleware(
            ServersConstants::PERMISSIONS_GROUP,
            ServersConstants::PERMISSION_SERVER,
            Permissions::ACCESS_EDIT
        ));

    $this
        ->post('/{server}/delete', BaseController::action(ServersController::class, 'delete'))
        ->setName(ServersConstants::ROUTE_DELETE)
        ->add($permissions->hasAccessToPermissionMiddleware(
            ServersConstants::PERMISSIONS_GROUP,
            ServersConstants::PERMISSION_SERVER,
            Permissions::ACCESS_DELETE
        ));

    $this->group('/{server}/groups', function () {
        /** @var \Slim\App $this */

        /** @var Permissions $permissions */
        $permissions = $this->getContainer()->get('permissions');

        $this
            ->get('', BaseController::action(GroupsController::class, 'index'))
            ->setName('admin_servers_groups_list')
            ->add($permissions->hasAccessToResourceMiddleware('server', 'admin', 'server_group', Permissions::ACCESS_LIST));

        $this
            ->map(['GET', 'POST'], '/create', BaseController::action(GroupsController::class, 'create'))
            ->setName('admin_servers_groups_create')
            ->add($permissions->hasAccessToResourceMiddleware('server', 'admin', 'server_group', Permissions::ACCESS_CREATE));

        $this
            ->map(['GET', 'POST'], '/{group}/edit', BaseController::action(GroupsController::class, 'edit'))
            ->setName('admin_servers_groups_edit')
            ->add($permissions->hasAccessToResourceMiddleware('server', 'admin', 'server_group', Permissions::ACCESS_EDIT));

        $this
            ->post('/{group}/delete', BaseController::action(GroupsController::class, 'delete'))
            ->setName('admin_servers_groups_delete')
            ->add($permissions->hasAccessToResourceMiddleware('server', 'admin', 'server_group', Permissions::ACCESS_DELETE));
    });
    
    $this->group('/{server}/reasons', function () {
        /** @var \Slim\App $this */

        /** @var Permissions $permissions */
        $permissions = $this->getContainer()->get('permissions');

        $this
            ->get('', BaseController::action(ReasonsController::class, 'index'))
            ->setName('admin_servers_reasons_list')
            ->add($permissions->hasAccessToResourceMiddleware('server', 'admin', 'server_reason', Permissions::ACCESS_LIST));
        
        $this
            ->map(['GET', 'POST'], '/create', BaseController::action(ReasonsController::class, 'create'))
            ->setName('admin_servers_reasons_create')
            ->add($permissions->hasAccessToResourceMiddleware('server', 'admin', 'server_reason', Permissions::ACCESS_CREATE));
        
        $this
            ->map(['GET', 'POST'], '/{reason}/edit', BaseController::action(ReasonsController::class, 'edit'))
            ->setName('admin_servers_reasons_edit')
            ->add($permissions->hasAccessToResourceMiddleware('server', 'admin', 'server_reason', Permissions::ACCESS_EDIT));
        
        $this
            ->post('/{reason}/delete', BaseController::action(ReasonsController::class, 'delete'))
            ->setName('admin_servers_reasons_delete')
            ->add($permissions->hasAccessToResourceMiddleware('server', 'admin', 'server_reason', Permissions::ACCESS_DELETE));
    });
};
