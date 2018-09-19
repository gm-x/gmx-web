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
	->map(['GET', 'POST'], '/settings/assign', BaseController::action(SettingsController::class, 'assign'))
	->setName(SettingsConstants::ROUTE_ASSIGN)
    ->add($permissions->isAuthorizedMiddleware());
