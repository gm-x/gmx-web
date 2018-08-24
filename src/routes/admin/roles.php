<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\Admin\RolesController;
use \GameX\Core\Auth\Permissions\Manager;
use \GameX\Core\Auth\Middlewares\HasAccessToPermission;

return function () {
    /** @var \Slim\App $this */
    $this
        ->get('', BaseController::action(RolesController::class, 'index'))
        ->setName('admin_roles_list')
        ->add(new HasAccessToPermission('admin', 'role', Manager::ACCESS_LIST));

    $this
        ->map(['GET', 'POST'], '/create', BaseController::action(RolesController::class, 'create'))
        ->setName('admin_roles_create')
        ->add(new HasAccessToPermission('admin', 'role', Manager::ACCESS_CREATE));

    $this
        ->map(['GET', 'POST'], '/{role}/edit', BaseController::action(RolesController::class, 'edit'))
        ->setName('admin_roles_edit')
        ->add(new HasAccessToPermission('admin', 'role', Manager::ACCESS_EDIT));

    $this
        ->post('/{role}/delete', BaseController::action(RolesController::class, 'delete'))
        ->setName('admin_roles_delete')
        ->add(new HasAccessToPermission('admin', 'role', Manager::ACCESS_DELETE));

    $this
        ->get('/{role}/users', BaseController::action(RolesController::class, 'users'))
        ->setName('admin_roles_users')
        ->add(new HasAccessToPermission('admin', 'user_role', Manager::ACCESS_LIST));

    $this
        ->map(['GET', 'POST'], '/{role}/permissions', BaseController::action(RolesController::class, 'permissions'))
        ->setName('admin_roles_permissions')
        ->setArgument('permission', 'admin.roles');
};
