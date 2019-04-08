<?php

namespace GameX\Routes\Admin;

use \Slim\App;
use \GameX\Core\BaseRoute;
use \GameX\Controllers\Admin\UsersController;
use \GameX\Core\Auth\Permissions;
use \GameX\Constants\Admin\UsersConstants;

class UsersRoutes extends BaseRoute
{
	public function __invoke(App $app)
	{
		$app
			->get('', [UsersController::class, 'index'])
			->setName(UsersConstants::ROUTE_LIST)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				UsersConstants::PERMISSION_GROUP,
				UsersConstants::PERMISSION_KEY,
				Permissions::ACCESS_LIST
			));

		$app
			->get('/{user:\d+}/view', [UsersController::class, 'view'])
			->setName(UsersConstants::ROUTE_VIEW)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				UsersConstants::PERMISSION_GROUP,
				UsersConstants::PERMISSION_KEY,
				Permissions::ACCESS_VIEW
			));

		$app
			->map(['GET', 'POST'], '/{user:\d+}/edit', [UsersController::class, 'edit'])
			->setName(UsersConstants::ROUTE_EDIT)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				UsersConstants::PERMISSION_GROUP,
				UsersConstants::PERMISSION_KEY,
				Permissions::ACCESS_EDIT
			));

		$app
			->post('/{user:\d+}/activate', [UsersController::class, 'activate'])
			->setName(UsersConstants::ROUTE_ACTIVATE)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				UsersConstants::PERMISSION_GROUP,
				UsersConstants::PERMISSION_KEY,
				Permissions::ACCESS_EDIT
			));
	}
}
