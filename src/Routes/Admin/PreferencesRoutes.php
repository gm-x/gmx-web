<?php

namespace GameX\Routes\Admin;

use \Slim\App;
use \GameX\Core\BaseRoute;
use \GameX\Controllers\Admin\PreferencesController;
use \GameX\Core\Auth\Permissions;
use \GameX\Constants\Admin\PreferencesConstants;

class PreferencesRoutes extends BaseRoute
{
	public function __invoke(App $app)
	{
		$app
			->get('', [PreferencesController::class, 'main'])
			->setName(PreferencesConstants::ROUTE_MAIN)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				PreferencesConstants::PERMISSION_GROUP,
				PreferencesConstants::PERMISSION_MAIN_KEY,
				Permissions::ACCESS_VIEW
			));

		$app
			->post('', [PreferencesController::class, 'main'])
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				PreferencesConstants::PERMISSION_GROUP,
				PreferencesConstants::PERMISSION_MAIN_KEY,
				Permissions::ACCESS_EDIT
			));

		$app
			->get('/email', [PreferencesController::class, 'email'])
			->setName(PreferencesConstants::ROUTE_EMAIL)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				PreferencesConstants::PERMISSION_GROUP,
				PreferencesConstants::PERMISSION_EMAIL_KEY,
				Permissions::ACCESS_VIEW
			));

		$app
			->post('/email', [PreferencesController::class, 'email'])
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				PreferencesConstants::PERMISSION_GROUP,
				PreferencesConstants::PERMISSION_EMAIL_KEY,
				Permissions::ACCESS_EDIT
			));

		$app
			->post('/email/test', [PreferencesController::class, 'test'])
			->setName(PreferencesConstants::ROUTE_EMAIL_TEST)
			->setArgument('csrf_skip', true)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				PreferencesConstants::PERMISSION_GROUP,
				PreferencesConstants::PERMISSION_EMAIL_KEY,
				Permissions::ACCESS_VIEW | Permissions::ACCESS_EDIT
			));

		$app
			->get('/update', [PreferencesController::class, 'update'])
			->setName(PreferencesConstants::ROUTE_UPDATE)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				PreferencesConstants::PERMISSION_GROUP,
				PreferencesConstants::PERMISSION_UPDATE_KEY,
				Permissions::ACCESS_VIEW
			));

		$app
			->post('/update', [PreferencesController::class, 'update'])
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				PreferencesConstants::PERMISSION_GROUP,
				PreferencesConstants::PERMISSION_UPDATE_KEY,
				Permissions::ACCESS_EDIT
			));

		$app
			->get('/cache', [PreferencesController::class, 'cache'])
			->setName(PreferencesConstants::ROUTE_CACHE)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				PreferencesConstants::PERMISSION_GROUP,
				PreferencesConstants::PERMISSION_CACHE_KEY,
				Permissions::ACCESS_VIEW
			));

		$app
			->post('/cache', [PreferencesController::class, 'cache'])
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				PreferencesConstants::PERMISSION_GROUP,
				PreferencesConstants::PERMISSION_CACHE_KEY,
				Permissions::ACCESS_EDIT
			));

		$app
			->get('/cron', [PreferencesController::class, 'cron'])
			->setName(PreferencesConstants::ROUTE_CRON)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				PreferencesConstants::PERMISSION_GROUP,
				PreferencesConstants::PERMISSION_CRON_KEY,
				Permissions::ACCESS_VIEW
			));

		$app
			->get('/social', [PreferencesController::class, 'social'])
			->setName(PreferencesConstants::ROUTE_SOCIAL)
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				PreferencesConstants::PERMISSION_GROUP,
				PreferencesConstants::PERMISSION_CACHE_KEY,
				Permissions::ACCESS_VIEW
			));

		$app
			->post('/social', [PreferencesController::class, 'social'])
			->add($this->getPermissions()->hasAccessToPermissionMiddleware(
				PreferencesConstants::PERMISSION_GROUP,
				PreferencesConstants::PERMISSION_SOCIAL_KEY,
				Permissions::ACCESS_EDIT
			));
	}
}

