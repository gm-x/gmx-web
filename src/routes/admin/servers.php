<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\Admin\ServersController;

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
        ->map(['GET', 'POST'], '/edit/{server}', BaseController::action(ServersController::class, 'edit'))
        ->setName('admin_servers_edit')
        ->setArgument('permission', 'admin.servers');

    $this
        ->post('/delete/{server}', BaseController::action(ServersController::class, 'delete'))
        ->setName('admin_servers_delete')
        ->setArgument('permission', 'admin.servers');
};
