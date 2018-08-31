<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\SettingsController;
use \GameX\Core\Auth\Middlewares\IsAuthorized;

/** @var \Slim\App $this */
$this
    ->map(['GET', 'POST'], '/settings', BaseController::action(SettingsController::class, 'index'))
    ->setName('user_settings_index')
	->add(new IsAuthorized());

$this
	->map(['GET', 'POST'], '/settings/email', BaseController::action(SettingsController::class, 'email'))
	->setName('user_settings_email')
    ->add(new IsAuthorized());

$this
	->map(['GET', 'POST'], '/settings/password', BaseController::action(SettingsController::class, 'password'))
	->setName('user_settings_password')
    ->add(new IsAuthorized());

$this
	->map(['GET', 'POST'], '/settings/avatar', BaseController::action(SettingsController::class, 'avatar'))
	->setName('user_settings_avatar')
    ->add(new IsAuthorized());

$this
	->map(['GET', 'POST'], '/settings/steamid', BaseController::action(SettingsController::class, 'steamid'))
	->setName('user_settings_steamid')
    ->add(new IsAuthorized());
