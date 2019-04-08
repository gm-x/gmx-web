<?php
use \GameX\Controllers\SettingsController;
use \GameX\Core\Auth\Permissions;
use \GameX\Constants\SettingsConstants;

/** @var \Slim\App $this */

/** @var Permissions $permissions */
$permissions = $this->getContainer()->get('permissions');

$this
    ->map(['GET', 'POST'], '/settings', [SettingsController::class, 'index'])
    ->setName(SettingsConstants::ROUTE_INDEX)
	->add($permissions->isAuthorizedMiddleware());
