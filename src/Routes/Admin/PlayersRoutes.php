<?php

namespace GameX\Routes\Admin;

use \Slim\App;
use \GameX\Core\BaseRoute;
use \GameX\Constants\Admin\PlayersConstants;
use \GameX\Constants\Admin\PrivilegesConstants;
use \GameX\Constants\Admin\PunishmentsConstants;
use \GameX\Controllers\Admin\PlayersController;
use \GameX\Controllers\Admin\PrivilegesController;
use \GameX\Controllers\Admin\PunishmentsController;
use \GameX\Core\Auth\Permissions;

class PlayersRoutes extends BaseRoute
{
	public function __invoke(App $app)
	{
		$app
			->get('', [PlayersController::class, 'index'])
			->setName(PlayersConstants::ROUTE_LIST)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				PlayersConstants::PERMISSION_GROUP,
				PlayersConstants::PERMISSION_KEY,
				Permissions::ACCESS_LIST
			));

		$app
			->get('/{player:\d+}/view', [PlayersController::class, 'view'])
			->setName(PlayersConstants::ROUTE_VIEW)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				PlayersConstants::PERMISSION_GROUP,
				PlayersConstants::PERMISSION_KEY,
				Permissions::ACCESS_VIEW
			));

		$app
			->map(['GET', 'POST'], '/create', [PlayersController::class, 'create'])
			->setName(PlayersConstants::ROUTE_CREATE)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				PlayersConstants::PERMISSION_GROUP,
				PlayersConstants::PERMISSION_KEY,
				Permissions::ACCESS_CREATE
			));

		$app
			->map(['GET', 'POST'], '/{player:\d+}/edit', [PlayersController::class, 'edit'])
			->setName(PlayersConstants::ROUTE_EDIT)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				PlayersConstants::PERMISSION_GROUP,
				PlayersConstants::PERMISSION_KEY,
				Permissions::ACCESS_EDIT
			));

		$app
			->post('/{player:\d+}/delete', [PlayersController::class, 'delete'])
			->setName(PlayersConstants::ROUTE_DELETE)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				PlayersConstants::PERMISSION_GROUP,
				PlayersConstants::PERMISSION_KEY,
				Permissions::ACCESS_DELETE
			));

		$app->group('/{player:\d+}/privileges', [$this, 'privileges']);
		$app->group('/{player:\d+}/punishments', [$this, 'punishments']);
	}

	public function privileges(App $app)
	{
		$app
			->get('', [PrivilegesController::class, 'index'])
			->setName(PrivilegesConstants::ROUTE_LIST);

		$app
			->map(['GET', 'POST'], '/create/{server:\d+}', [PrivilegesController::class, 'create'])
			->setName(PrivilegesConstants::ROUTE_CREATE)
			->add($this->getPermissions()->hasAccessToResourceMiddleware(
				'server',
				PrivilegesConstants::PERMISSION_GROUP,
				PrivilegesConstants::PERMISSION_KEY,
				Permissions::ACCESS_CREATE
			));

		$app
			->map(['GET', 'POST'], '/{privilege:\d+}/edit', [PrivilegesController::class, 'edit'])
			->setName(PrivilegesConstants::ROUTE_EDIT);

		$app
			->post('/{privilege:\d+}/delete', [PrivilegesController::class, 'delete'])
			->setName(PrivilegesConstants::ROUTE_DELETE);
	}

	public function punishments(App $app)
	{
		$app
			->get('/{punishment:\d+}', [PunishmentsController::class, 'view'])
			->setName(PunishmentsConstants::ROUTE_VIEW);

		$app
			->map(['GET', 'POST'], '/create/{server:\d+}', [PunishmentsController::class, 'create'])
			->setName(PunishmentsConstants::ROUTE_CREATE)
			->add($this->getPermissions()->hasAccessToResourceMiddleware(
				'server',
				PunishmentsConstants::PERMISSION_GROUP,
				PunishmentsConstants::PERMISSION_KEY,
				Permissions::ACCESS_CREATE
			));

		$app
			->map(['GET', 'POST'], '/{punishment:\d+}/edit', [PunishmentsController::class, 'edit'])
			->setName(PunishmentsConstants::ROUTE_EDIT);

		// TODO: Add permissions
		$app
			->map(['GET', 'POST'], '/{punishment:\d+}/amnesty', [PunishmentsController::class, 'amnesty'])
			->setName(PunishmentsConstants::ROUTE_AMNESTY);

		$app
			->post('/{punishment:\d+}/delete', [PunishmentsController::class, 'delete'])
			->setName(PunishmentsConstants::ROUTE_DELETE);
	}
}
