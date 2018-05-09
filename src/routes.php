<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\IndexController;
use \GameX\Controllers\UserController;

//$app
//    ->get('/', BaseController::action(IndexController::class, 'index'))
//    ->setName('index');

$app
    ->map(['GET', 'POST'], '/', BaseController::action(IndexController::class, 'index'))
    ->setName('index');

$app
    ->map(['GET', 'POST'], '/register', BaseController::action(UserController::class, 'register'))
    ->setName('register');

$app
	->map(['GET', 'POST'], '/activation/{code}', BaseController::action(UserController::class, 'activate'))
    ->setName('activation');

$app
	->map(['GET', 'POST'], '/login', BaseController::action(UserController::class, 'login'))
    ->setName('login');

$app
	->map(['GET', 'POST'], '/reset_password', BaseController::action(UserController::class, 'resetPassword'))
    ->setName('reset_password');

$app
    ->map(['GET', 'POST'], '/reset_password/{code}', BaseController::action(UserController::class, 'resetPasswordComplete'))
    ->setName('reset_password_complete');

$app
	->get('/logout', BaseController::action(UserController::class, 'logout'))
    ->setName('logout');
