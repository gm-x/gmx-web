<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\Admin\RolesController;
use \GameX\Controllers\Admin\PermissionsController;
use \GameX\Core\Auth\Permissions;
use \GameX\Constants\Admin\RolesConstants;
use \GameX\Constants\Admin\PermissionsConstants;

return function () {
    /** @var \Slim\App $this */

    /** @var Permissions $permissions */
    $permissions = $this->getContainer()->get('permissions');

    $this
        ->get('', BaseController::action(RolesController::class, 'index'))
        ->setName(RolesConstants::ROUTE_LIST)
        ->add($permissions->hasAccessToPermissionMiddleware(
            RolesConstants::PERMISSION_GROUP,
            RolesConstants::PERMISSION_KEY,
            Permissions::ACCESS_LIST
        ));
    
    $this
        ->get('/{role}/view', BaseController::action(RolesController::class, 'view'))
        ->setName(RolesConstants::ROUTE_VIEW)
        ->add($permissions->hasAccessToPermissionMiddleware(
            RolesConstants::PERMISSION_GROUP,
            RolesConstants::PERMISSION_KEY,
            Permissions::ACCESS_VIEW
        ));

    $this
        ->map(['GET', 'POST'], '/create', BaseController::action(RolesController::class, 'create'))
        ->setName(RolesConstants::ROUTE_CREATE)
        ->add($permissions->hasAccessToPermissionMiddleware(
            RolesConstants::PERMISSION_GROUP,
            RolesConstants::PERMISSION_KEY,
            Permissions::ACCESS_CREATE
        ));

    $this
        ->map(['GET', 'POST'], '/{role}/edit', BaseController::action(RolesController::class, 'edit'))
        ->setName(RolesConstants::ROUTE_EDIT)
        ->add($permissions->hasAccessToPermissionMiddleware(
            RolesConstants::PERMISSION_GROUP,
            RolesConstants::PERMISSION_KEY,
            Permissions::ACCESS_EDIT
        ));

    $this
        ->post('/{role}/delete', BaseController::action(RolesController::class, 'delete'))
        ->setName(RolesConstants::ROUTE_DELETE)
        ->add($permissions->hasAccessToPermissionMiddleware(
            RolesConstants::PERMISSION_GROUP,
            RolesConstants::PERMISSION_KEY,
            Permissions::ACCESS_DELETE
        ));

    $this->group('/{role}/permissions', function () {
        /** @var \Slim\App $this */

        /** @var Permissions $permissions */
        $permissions = $this->getContainer()->get('permissions');

        $this->map(['GET', 'POST'], '', BaseController::action(PermissionsController::class, 'index'))
            ->setName(PermissionsConstants::ROUTE_LIST)
            ->add($permissions->hasAccessToPermissionMiddleware(
                PermissionsConstants::PERMISSION_GROUP,
                PermissionsConstants::PERMISSION_KEY,
                Permissions::ACCESS_LIST | Permissions::ACCESS_EDIT
            ));
    });
};
