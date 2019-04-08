<?php
use \GameX\Controllers\UserController;
use \GameX\Constants\UserConstants;

$this
    ->map(['GET', 'POST'], '/register', [UserController::class, 'register'])
    ->setName('register');

$this
    ->map(['GET', 'POST'], '/activation/{code}', [UserController::class, 'activate'])
    ->setName('activation');

$this
    ->map(['GET', 'POST'], '/login', [UserController::class, 'login'])
    ->setName('login');

$this
    ->map(['GET', 'POST'], '/reset_password', [UserController::class, 'resetPassword'])
    ->setName('reset_password');

$this
    ->map(['GET', 'POST'], '/reset_password/{code}', [UserController::class, 'resetPasswordComplete'])
    ->setName('reset_password_complete');

$this
    ->get('/logout', [UserController::class, 'logout'])
    ->setName('logout');

$this
	->map(['GET', 'POST'], '/auth/{provider}', [UserController::class, 'social'])
	->setName(UserConstants::ROUTE_SOCIAL);
