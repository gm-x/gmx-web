<?php
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
        ->get('', [RolesController::class, 'index'])
        ->setName(RolesConstants::ROUTE_LIST)
        ->add($permissions->hasAccessToPermissionMiddleware(
            RolesConstants::PERMISSION_GROUP,
            RolesConstants::PERMISSION_KEY,
            Permissions::ACCESS_LIST
        ));
    
    $this
        ->get('/{role:\d+}/view', [RolesController::class, 'view'])
        ->setName(RolesConstants::ROUTE_VIEW)
        ->add($permissions->hasAccessToPermissionMiddleware(
            RolesConstants::PERMISSION_GROUP,
            RolesConstants::PERMISSION_KEY,
            Permissions::ACCESS_VIEW
        ));

    $this
        ->map(['GET', 'POST'], '/create', [RolesController::class, 'create'])
        ->setName(RolesConstants::ROUTE_CREATE)
        ->add($permissions->hasAccessToPermissionMiddleware(
            RolesConstants::PERMISSION_GROUP,
            RolesConstants::PERMISSION_KEY,
            Permissions::ACCESS_CREATE
        ));

    $this
        ->map(['GET', 'POST'], '/{role:\d+}/edit', [RolesController::class, 'edit'])
        ->setName(RolesConstants::ROUTE_EDIT)
        ->add($permissions->hasAccessToPermissionMiddleware(
            RolesConstants::PERMISSION_GROUP,
            RolesConstants::PERMISSION_KEY,
            Permissions::ACCESS_EDIT
        ));

    $this
        ->post('/{role:\d+}/delete', [RolesController::class, 'delete'])
        ->setName(RolesConstants::ROUTE_DELETE)
        ->add($permissions->hasAccessToPermissionMiddleware(
            RolesConstants::PERMISSION_GROUP,
            RolesConstants::PERMISSION_KEY,
            Permissions::ACCESS_DELETE
        ));

    $this->group('/{role:\d+}/permissions', function () {
        /** @var \Slim\App $this */

        /** @var Permissions $permissions */
        $permissions = $this->getContainer()->get('permissions');

        $this->get('', [PermissionsController::class, 'index'])
            ->setName(PermissionsConstants::ROUTE_LIST)
            ->add($permissions->hasAccessToPermissionMiddleware(
                PermissionsConstants::PERMISSION_GROUP,
                PermissionsConstants::PERMISSION_KEY,
                Permissions::ACCESS_LIST
            ));
    
        $this->post('', [PermissionsController::class, 'index'])
            ->add($permissions->hasAccessToPermissionMiddleware(
                PermissionsConstants::PERMISSION_GROUP,
                PermissionsConstants::PERMISSION_KEY,
                Permissions::ACCESS_EDIT
            ));
    });
};
