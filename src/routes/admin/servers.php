<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\Admin\ServersController;
use \GameX\Controllers\Admin\GroupsController;

return function () {
    /** @var \Slim\App $this */
    $this
        ->get('', BaseController::action(ServersController::class, 'index'))
        ->setName('admin_servers_list')
        ->setArgument('permission', 'admin.servers');

    $this
        ->map(['GET', 'POST'], '/create', BaseController::action(ServersController::class, 'create'))
        ->setName('admin_servers_create')
        ->setArgument('permission', 'admin.servers');

    $this
        ->map(['GET', 'POST'], '/{server}/edit', BaseController::action(ServersController::class, 'edit'))
        ->setName('admin_servers_edit')
        ->setArgument('permission', 'admin.servers');

    $this
        ->post('/{server}/delete', BaseController::action(ServersController::class, 'delete'))
        ->setName('admin_servers_delete')
        ->setArgument('permission', 'admin.servers');

    $this
        ->get('/{server}/config', BaseController::action(ServersController::class, 'config'))
        ->setName('admin_servers_config')
        ->setArgument('permission', 'admin.servers');

    $this->group('/{server}/groups', function () {
        /** @var \Slim\App $this */
        $this
            ->get('', BaseController::action(GroupsController::class, 'index'))
            ->setName('admin_servers_groups_list')
            ->setArgument('permission', 'admin.servers.groups');

        $this
            ->map(['GET', 'POST'], '/create', BaseController::action(GroupsController::class, 'create'))
            ->setName('admin_servers_groups_create')
			->setArgument('permission', 'admin.servers.groups');

        $this
            ->map(['GET', 'POST'], '/{group}/edit', BaseController::action(GroupsController::class, 'edit'))
            ->setName('admin_servers_groups_edit')
			->setArgument('permission', 'admin.servers.groups');

        $this
            ->post('/{group}/delete', BaseController::action(GroupsController::class, 'delete'))
            ->setName('admin_servers_groups_delete')
			->setArgument('permission', 'admin.servers.groups');
    });
};
