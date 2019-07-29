<?php

namespace GameX\Routes\Admin;

use \Slim\App;
use \GameX\Core\BaseRoute;
use \GameX\Controllers\Admin\RolesController;
use \GameX\Controllers\Admin\PermissionsController;
use \GameX\Core\Auth\Permissions;
use \GameX\Constants\Admin\RolesConstants;
use \GameX\Constants\Admin\PermissionsConstants;

class RolesRoutes extends BaseRoute
{
	public function __invoke(App $app)
	{
		$app
			->get('', [RolesController::class, 'index'])
			->setName(RolesConstants::ROUTE_LIST)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				RolesConstants::PERMISSION_GROUP,
				RolesConstants::PERMISSION_KEY,
				Permissions::ACCESS_LIST
			));

		$app
			->get('/{role:\d+}/view', [RolesController::class, 'view'])
			->setName(RolesConstants::ROUTE_VIEW)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				RolesConstants::PERMISSION_GROUP,
				RolesConstants::PERMISSION_KEY,
				Permissions::ACCESS_VIEW
			));

        $app->post('/{role:\d+}/view', [RolesController::class, 'view'])
            ->add($this->getPermissions()->hasAccessToPermissionMiddleware(
                PermissionsConstants::PERMISSION_GROUP,
                PermissionsConstants::PERMISSION_KEY,
                Permissions::ACCESS_EDIT
            ));

		$app
			->map(['GET', 'POST'], '/create', [RolesController::class, 'create'])
			->setName(RolesConstants::ROUTE_CREATE)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				RolesConstants::PERMISSION_GROUP,
				RolesConstants::PERMISSION_KEY,
				Permissions::ACCESS_CREATE
			));

		$app
			->map(['GET', 'POST'], '/{role:\d+}/edit', [RolesController::class, 'edit'])
			->setName(RolesConstants::ROUTE_EDIT)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				RolesConstants::PERMISSION_GROUP,
				RolesConstants::PERMISSION_KEY,
				Permissions::ACCESS_EDIT
			));

		$app
			->post('/{role:\d+}/delete', [RolesController::class, 'delete'])
			->setName(RolesConstants::ROUTE_DELETE)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				RolesConstants::PERMISSION_GROUP,
				RolesConstants::PERMISSION_KEY,
				Permissions::ACCESS_DELETE
			));

		$app->group('/{role:\d+}/permissions', [$this, 'permissions']);
	}

	public function permissions(App $app)
	{
		$app->get('', [PermissionsController::class, 'index'])
			->setName(PermissionsConstants::ROUTE_LIST)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				PermissionsConstants::PERMISSION_GROUP,
				PermissionsConstants::PERMISSION_KEY,
				Permissions::ACCESS_LIST
			));

		$app->post('', [PermissionsController::class, 'index'])
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				PermissionsConstants::PERMISSION_GROUP,
				PermissionsConstants::PERMISSION_KEY,
				Permissions::ACCESS_EDIT
			));
	}
}
