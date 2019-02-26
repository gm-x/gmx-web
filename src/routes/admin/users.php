<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\Admin\UsersController;
use \GameX\Core\Auth\Permissions;
use \GameX\Constants\Admin\UsersConstants;

return function () {
    /** @var \Slim\App $this */

    /** @var Permissions $permissions */
    $permissions = $this->getContainer()->get('permissions');

    $this
        ->get('', BaseController::action(UsersController::class, 'index'))
        ->setName(UsersConstants::ROUTE_LIST)
        ->add($permissions->hasAccessToPermissionMiddleware(
            UsersConstants::PERMISSION_GROUP,
            UsersConstants::PERMISSION_KEY,
            Permissions::ACCESS_LIST
        ));

    $this
        ->get('/{user}/view', BaseController::action(UsersController::class, 'view'))
        ->setName(UsersConstants::ROUTE_VIEW)
        ->add($permissions->hasAccessToPermissionMiddleware(
            UsersConstants::PERMISSION_GROUP,
            UsersConstants::PERMISSION_KEY,
            Permissions::ACCESS_VIEW
        ));

    $this
        ->map(['GET', 'POST'], '/{user}/edit', BaseController::action(UsersController::class, 'edit'))
        ->setName(UsersConstants::ROUTE_EDIT)
        ->add($permissions->hasAccessToPermissionMiddleware(
            UsersConstants::PERMISSION_GROUP,
            UsersConstants::PERMISSION_KEY,
            Permissions::ACCESS_EDIT
        ));

    $this
        ->post('/{user}/activate', BaseController::action(UsersController::class, 'activate'))
        ->setName(UsersConstants::ROUTE_ACTIVATE)
        ->add($permissions->hasAccessToPermissionMiddleware(
            UsersConstants::PERMISSION_GROUP,
            UsersConstants::PERMISSION_KEY,
            Permissions::ACCESS_EDIT
        ));
};
