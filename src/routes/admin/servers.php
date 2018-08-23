<?php
use \GameX\Core\BaseController;
use \GameX\Core\Auth\Permissions\Manager;
use \GameX\Core\Auth\Middlewares\HasAccessToPermission;
use \GameX\Core\Auth\Middlewares\HasAccessToResource;
use \GameX\Controllers\Admin\ServersController;
use \GameX\Controllers\Admin\GroupsController;
use \GameX\Controllers\Admin\ReasonsController;

return function () {
    /** @var \Slim\App $this */
    $this
        ->get('', BaseController::action(ServersController::class, 'index'))
        ->setName('admin_servers_list')
        ->add(new HasAccessToPermission('admin', 'server', Manager::ACCESS_LIST));
    
    $this
        ->get('/token', BaseController::action(ServersController::class, 'token'))
        ->setName('admin_servers_token')
        ->setArgument('permission', 'admin.servers'); // TODO: need another permission

    $this
        ->map(['GET', 'POST'], '/create', BaseController::action(ServersController::class, 'create'))
        ->setName('admin_servers_create')
        ->add(new HasAccessToPermission('admin', 'server', Manager::ACCESS_CREATE));

    $this
        ->map(['GET', 'POST'], '/{server}/edit', BaseController::action(ServersController::class, 'edit'))
        ->setName('admin_servers_edit')
        ->add(new HasAccessToPermission('admin', 'server', Manager::ACCESS_EDIT));

    $this
        ->post('/{server}/delete', BaseController::action(ServersController::class, 'delete'))
        ->setName('admin_servers_delete')
        ->add(new HasAccessToPermission('admin', 'server', Manager::ACCESS_DELETE));

    $this->group('/{server}/groups', function () {
        /** @var \Slim\App $this */
        $this
            ->get('', BaseController::action(GroupsController::class, 'index'))
            ->setName('admin_servers_groups_list')
            ->add(new HasAccessToResource('server', 'admin', 'server_group', Manager::ACCESS_LIST));

        $this
            ->map(['GET', 'POST'], '/create', BaseController::action(GroupsController::class, 'create'))
            ->setName('admin_servers_groups_create')
            ->add(new HasAccessToResource('server', 'admin', 'server_group', Manager::ACCESS_CREATE));

        $this
            ->map(['GET', 'POST'], '/{group}/edit', BaseController::action(GroupsController::class, 'edit'))
            ->setName('admin_servers_groups_edit')
            ->add(new HasAccessToResource('server', 'admin', 'server_group', Manager::ACCESS_EDIT));

        $this
            ->post('/{group}/delete', BaseController::action(GroupsController::class, 'delete'))
            ->setName('admin_servers_groups_delete')
            ->add(new HasAccessToResource('server', 'admin', 'server_group', Manager::ACCESS_DELETE));
    });
    
    $this->group('/{server}/reasons', function () {
        /** @var \Slim\App $this */
        $this
            ->get('', BaseController::action(ReasonsController::class, 'index'))
            ->setName('admin_servers_reasons_list')
            ->setArgument('permission', 'admin.servers.reasons');
        
        $this
            ->map(['GET', 'POST'], '/create', BaseController::action(ReasonsController::class, 'create'))
            ->setName('admin_servers_reasons_create')
            ->setArgument('permission', 'admin.servers.reasons');
        
        $this
            ->map(['GET', 'POST'], '/{reason}/edit', BaseController::action(ReasonsController::class, 'edit'))
            ->setName('admin_servers_reasons_edit')
            ->setArgument('permission', 'admin.servers.reasons');
        
        $this
            ->post('/{reason}/delete', BaseController::action(ReasonsController::class, 'delete'))
            ->setName('admin_servers_reasons_delete')
            ->setArgument('permission', 'admin.servers.reasons');
    });
};
