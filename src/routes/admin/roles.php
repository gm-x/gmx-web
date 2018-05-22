<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\Admin\RolesController;

return function () {
    /** @var \Slim\App $this */
    $this
        ->get('', BaseController::action(RolesController::class, 'index'))
        ->setName('admin_roles_list')
        ->setArgument('permission', 'admin.roles');

    $this
        ->map(['GET', 'POST'], '/create', BaseController::action(RolesController::class, 'create'))
        ->setName('admin_roles_create')
        ->setArgument('permission', 'admin.roles');

    $this
        ->map(['GET', 'POST'], '/edit/{role}', BaseController::action(RolesController::class, 'edit'))
        ->setName('admin_roles_edit')
        ->setArgument('permission', 'admin.roles');

    $this
        ->post('/delete/{role}', BaseController::action(RolesController::class, 'delete'))
        ->setName('admin_roles_delete')
        ->setArgument('permission', 'admin.roles');

    $this
        ->get('/users/{role}', BaseController::action(RolesController::class, 'users'))
        ->setName('admin_roles_users')
        ->setArgument('permission', 'admin.roles');

    $this
        ->map(['GET', 'POST'], '/permissions/{role}', BaseController::action(RolesController::class, 'permissions'))
        ->setName('admin_roles_permissions')
        ->setArgument('permission', 'admin.roles');
};
