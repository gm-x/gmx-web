<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\SettingsController;
use \GameX\Core\Auth\Permissions;

/** @var \Slim\App $this */

/** @var Permissions $permissions */
$permissions = $this->getContainer()->get('permissions');

$this
    ->map(['GET', 'POST'], '/settings', BaseController::action(SettingsController::class, 'index'))
    ->setName('user_settings_index')
	->add($permissions->isAuthorizedMiddleware());

$this
	->map(['GET', 'POST'], '/settings/email', BaseController::action(SettingsController::class, 'email'))
	->setName('user_settings_email')
    ->add($permissions->isAuthorizedMiddleware());

$this
	->map(['GET', 'POST'], '/settings/password', BaseController::action(SettingsController::class, 'password'))
	->setName('user_settings_password')
    ->add($permissions->isAuthorizedMiddleware());

$this
	->map(['GET', 'POST'], '/settings/avatar', BaseController::action(SettingsController::class, 'avatar'))
	->setName('user_settings_avatar')
    ->add($permissions->isAuthorizedMiddleware());

$this
	->map(['GET', 'POST'], '/settings/steamid', BaseController::action(SettingsController::class, 'steamid'))
	->setName('user_settings_steamid')
    ->add($permissions->isAuthorizedMiddleware());
