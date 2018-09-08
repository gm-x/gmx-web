<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\Admin\RolesController;
use \GameX\Controllers\Admin\PermissionsController;
use \GameX\Core\Auth\Permissions;

return function () {
    /** @var \Slim\App $this */

    /** @var Permissions $permissions */
    $permissions = $this->getContainer()->get('permissions');

    $this
        ->get('', BaseController::action(RolesController::class, 'index'))
        ->setName('admin_roles_list')
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'role', Permissions::ACCESS_LIST));

    $this
        ->map(['GET', 'POST'], '/create', BaseController::action(RolesController::class, 'create'))
        ->setName('admin_roles_create')
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'role', Permissions::ACCESS_CREATE));

    $this
        ->map(['GET', 'POST'], '/{role}/edit', BaseController::action(RolesController::class, 'edit'))
        ->setName('admin_roles_edit')
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'role', Permissions::ACCESS_EDIT));

    $this
        ->post('/{role}/delete', BaseController::action(RolesController::class, 'delete'))
        ->setName('admin_roles_delete')
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'role', Permissions::ACCESS_DELETE));

    $this
        ->get('/{role}/users', BaseController::action(RolesController::class, 'users'))
        ->setName('admin_roles_users')
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'user_role', Permissions::ACCESS_LIST));

    $this->group('/{role}/permissions', function () {
        /** @var \Slim\App $this */

        /** @var Permissions $permissions */
        $permissions = $this->getContainer()->get('permissions');

        $this->map(['GET', 'POST'], '', BaseController::action(PermissionsController::class, 'index'))
            ->setName('admin_role_permissions')
            ->add($permissions->hasAccessToPermissionMiddleware('admin', 'role_permission', Permissions::ACCESS_LIST));
    });
};
