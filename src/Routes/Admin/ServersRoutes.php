<?php

namespace GameX\Routes\Admin;

use \Slim\App;
use \GameX\Core\BaseRoute;
use \GameX\Constants\Admin\ServersConstants;
use \GameX\Constants\Admin\GroupsConstants;
use \GameX\Constants\Admin\ReasonsConstants;
use \GameX\Constants\Admin\AccessConstants;
use \GameX\Core\Auth\Permissions;
use \GameX\Controllers\Admin\ServersController;
use \GameX\Controllers\Admin\GroupsController;
use \GameX\Controllers\Admin\ReasonsController;
use \GameX\Controllers\Admin\AccessController;

class ServersRoutes extends BaseRoute
{
	public function __invoke(App $app)
	{
		$app
			->get('', [ServersController::class, 'index'])
			->setName(ServersConstants::ROUTE_LIST)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				ServersConstants::PERMISSION_GROUP,
				ServersConstants::PERMISSION_KEY,
				Permissions::ACCESS_LIST
			));

		$app
			->get('/{server:\d+}/token', [ServersController::class, 'token'])
			->setName(ServersConstants::ROUTE_TOKEN)
			->add($this->getPermissions()->hasAccessToResourceMiddleware(
				'server',
				ServersConstants::PERMISSION_TOKEN_GROUP,
				ServersConstants::PERMISSION_TOKEN_KEY,
				Permissions::ACCESS_CREATE | Permissions::ACCESS_CREATE
			));

		$app
			->get('/{server:\d+}/view', [ServersController::class, 'view'])
			->setName(ServersConstants::ROUTE_VIEW)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				ServersConstants::PERMISSION_GROUP,
				ServersConstants::PERMISSION_KEY,
				Permissions::ACCESS_VIEW
			));

		$app
			->map(['GET', 'POST'], '/create', [ServersController::class, 'create'])
			->setName(ServersConstants::ROUTE_CREATE)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				ServersConstants::PERMISSION_GROUP,
				ServersConstants::PERMISSION_KEY,
				Permissions::ACCESS_CREATE
			));

		$app
			->map(['GET', 'POST'], '/{server:\d+}/edit', [ServersController::class, 'edit'])
			->setName(ServersConstants::ROUTE_EDIT)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				ServersConstants::PERMISSION_GROUP,
				ServersConstants::PERMISSION_KEY,
				Permissions::ACCESS_EDIT
			));

		$app
			->post('/{server:\d+}/delete', [ServersController::class, 'delete'])
			->setName(ServersConstants::ROUTE_DELETE)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				ServersConstants::PERMISSION_GROUP,
				ServersConstants::PERMISSION_KEY,
				Permissions::ACCESS_DELETE
			));

		$app->group('/{server:\d+}/groups', [$this, 'groups']);
		$app->group('/{server:\d+}/reasons', [$this, 'reasons']);
		$app->group('/{server:\d+}/access', [$this, 'access']);
	}
	
	public function groups(App $app)
	{
		$app
			->map(['GET', 'POST'], '/create', [GroupsController::class, 'create'])
			->setName(GroupsConstants::ROUTE_CREATE)
			->add($this->getPermissions()->hasAccessToResourceMiddleware(
				'server',
				GroupsConstants::PERMISSION_GROUP,
				GroupsConstants::PERMISSION_KEY,
				Permissions::ACCESS_CREATE
			));

		$app
			->map(['GET', 'POST'], '/{group:\d+}/edit', [GroupsController::class, 'edit'])
			->setName(GroupsConstants::ROUTE_EDIT)
			->add($this->getPermissions()->hasAccessToResourceMiddleware(
				'server',
				GroupsConstants::PERMISSION_GROUP,
				GroupsConstants::PERMISSION_KEY,
				Permissions::ACCESS_EDIT
			));

		$app
			->post('/{group:\d+}/delete', [GroupsController::class, 'delete'])
			->setName(GroupsConstants::ROUTE_DELETE)
			->add($this->getPermissions()->hasAccessToResourceMiddleware(
				'server',
				GroupsConstants::PERMISSION_GROUP,
				GroupsConstants::PERMISSION_KEY,
				Permissions::ACCESS_DELETE
			));

		$app
			->post('/priority', [GroupsController::class, 'priority'])
			->setName(GroupsConstants::ROUTE_PRIORITY)
			->add($this->getPermissions()->hasAccessToResourceMiddleware(
				'server',
				GroupsConstants::PERMISSION_GROUP,
				GroupsConstants::PERMISSION_KEY,
				Permissions::ACCESS_EDIT
			));
	}
	
	public function reasons(App $app)
	{
		$app
			->map(['GET', 'POST'], '/create', [ReasonsController::class, 'create'])
			->setName(ReasonsConstants::ROUTE_CREATE)
			->add($this->getPermissions()->hasAccessToResourceMiddleware(
				'server',
				ReasonsConstants::PERMISSION_GROUP,
				ReasonsConstants::PERMISSION_KEY,
				Permissions::ACCESS_CREATE
			));

		$app
			->map(['GET', 'POST'], '/{reason:\d+}/edit', [ReasonsController::class, 'edit'])
			->setName(ReasonsConstants::ROUTE_EDIT)
			->add($this->getPermissions()->hasAccessToResourceMiddleware(
				'server',
				ReasonsConstants::PERMISSION_GROUP,
				ReasonsConstants::PERMISSION_KEY,
				Permissions::ACCESS_EDIT
			));

		$app
			->post('/{reason:\d+}/delete', [ReasonsController::class, 'delete'])
			->setName(ReasonsConstants::ROUTE_DELETE)
			->add($this->getPermissions()->hasAccessToResourceMiddleware(
				'server',
				ReasonsConstants::PERMISSION_GROUP,
				ReasonsConstants::PERMISSION_KEY,
				Permissions::ACCESS_DELETE
			));
	}

	public function access(App $app)
	{
		$app
			->map(['GET', 'POST'], '/create', [AccessController::class, 'create'])
			->setName(AccessConstants::ROUTE_CREATE)
			->add($this->getPermissions()->hasAccessToResourceMiddleware(
				'server',
				AccessConstants::PERMISSION_GROUP,
				AccessConstants::PERMISSION_KEY,
				Permissions::ACCESS_CREATE
			));

		$app
			->map(['GET', 'POST'], '/{access:\d+}/edit', [AccessController::class, 'edit'])
			->setName(AccessConstants::ROUTE_EDIT)
			->add($this->getPermissions()->hasAccessToResourceMiddleware(
				'server',
				AccessConstants::PERMISSION_GROUP,
				AccessConstants::PERMISSION_KEY,
				Permissions::ACCESS_EDIT
			));

		$app
			->post('/{access:\d+}/delete', [AccessController::class, 'delete'])
			->setName(AccessConstants::ROUTE_DELETE)
			->add($this->getPermissions()->hasAccessToResourceMiddleware(
				'server',
				AccessConstants::PERMISSION_GROUP,
				AccessConstants::PERMISSION_KEY,
				Permissions::ACCESS_DELETE
			));
	}
}
