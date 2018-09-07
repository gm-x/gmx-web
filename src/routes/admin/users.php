<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\Admin\UsersController;
use \GameX\Core\Auth\Permissions;

return function () {
    /** @var \Slim\App $this */

    /** @var Permissions $permissions */
    $permissions = $this->getContainer()->get('permissions');

    $this
        ->get('', BaseController::action(UsersController::class, 'index'))
        ->setName('admin_users_list')
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'user', Permissions::ACCESS_LIST));

    $this
        ->get('/{user}/view', BaseController::action(UsersController::class, 'view'))
        ->setName('admin_users_view')
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'user', Permissions::ACCESS_VIEW));

    $this
        ->map(['GET', 'POST'], '/{user}/edit', BaseController::action(UsersController::class, 'edit'))
        ->setName('admin_users_edit')
        ->add($permissions->hasAccessToPermissionMiddleware('admin', 'user_role', Permissions::ACCESS_VIEW | Permissions::ACCESS_EDIT));
};
