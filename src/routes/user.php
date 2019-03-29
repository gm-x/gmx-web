<?php

use \GameX\Core\BaseController;
use \GameX\Controllers\UserController;
use \GameX\Constants\UserConstants;

$this
    ->map(['GET', 'POST'], '/register', BaseController::action(UserController::class, 'register'))
    ->setName('register');

$this
    ->map(['GET', 'POST'], '/activation/{code}', BaseController::action(UserController::class, 'activate'))
    ->setName('activation');

$this
    ->map(['GET', 'POST'], '/login', BaseController::action(UserController::class, 'login'))
    ->setName('login');

$this
    ->map(['GET', 'POST'], '/reset_password', BaseController::action(UserController::class, 'resetPassword'))
    ->setName('reset_password');

$this
    ->map(['GET', 'POST'], '/reset_password/{code}', BaseController::action(UserController::class, 'resetPasswordComplete'))
    ->setName('reset_password_complete');

$this
    ->get('/logout', BaseController::action(UserController::class, 'logout'))
    ->setName('logout');

$this
	->map(['GET', 'POST'], '/auth/{provider}', BaseController::action(UserController::class, 'social'))
	->setName(UserConstants::ROUTE_SOCIAL);
