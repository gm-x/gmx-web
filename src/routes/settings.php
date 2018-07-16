<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\SettingsController;

/** @var \Slim\App $this */
$this
    ->map(['GET', 'POST'], '/settings', BaseController::action(SettingsController::class, 'index'))
    ->setName('user_settings_index')
	->setArgument('is_authorized', true);

$this
	->map(['GET', 'POST'], '/settings/email', BaseController::action(SettingsController::class, 'email'))
	->setName('user_settings_email')
	->setArgument('is_authorized', true);

$this
	->map(['GET', 'POST'], '/settings/password', BaseController::action(SettingsController::class, 'password'))
	->setName('user_settings_password')
	->setArgument('is_authorized', true);

$this
	->map(['GET', 'POST'], '/settings/avatar', BaseController::action(SettingsController::class, 'avatar'))
	->setName('user_settings_avatar')
	->setArgument('is_authorized', true);

$this
	->map(['GET', 'POST'], '/settings/steamid', BaseController::action(SettingsController::class, 'steamid'))
	->setName('user_settings_steamid')
	->setArgument('is_authorized', true);
