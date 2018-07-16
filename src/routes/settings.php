<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\SettingsController;

/** @var \Slim\App $this */
$this
    ->map(['GET', 'POST'], '/settings', BaseController::action(SettingsController::class, 'index'))
    ->setName('user_settings_index');
