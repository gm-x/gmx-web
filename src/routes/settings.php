<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\SettingsController;
use \GameX\Core\Auth\Permissions;
use \GameX\Constants\SettingsConstants;

/** @var \Slim\App $this */

/** @var Permissions $permissions */
$permissions = $this->getContainer()->get('permissions');

$this
    ->map(['GET', 'POST'], '/settings', BaseController::action(SettingsController::class, 'index'))
    ->setName(SettingsConstants::ROUTE_MAIN)
	->add($permissions->isAuthorizedMiddleware());

$this
	->map(['GET', 'POST'], '/settings/email', BaseController::action(SettingsController::class, 'email'))
	->setName(SettingsConstants::ROUTE_EMAIL)
    ->add($permissions->isAuthorizedMiddleware());

$this
	->map(['GET', 'POST'], '/settings/password', BaseController::action(SettingsController::class, 'password'))
	->setName(SettingsConstants::ROUTE_PASSWORD)
    ->add($permissions->isAuthorizedMiddleware());

$this
	->map(['GET', 'POST'], '/settings/avatar', BaseController::action(SettingsController::class, 'avatar'))
	->setName(SettingsConstants::ROUTE_AVATAR)
    ->add($permissions->isAuthorizedMiddleware());

$this
	->map(['GET', 'POST'], '/settings/steamid', BaseController::action(SettingsController::class, 'steamid'))
	->setName(SettingsConstants::ROUTE_CONNECT)
    ->add($permissions->isAuthorizedMiddleware());
