<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\IndexController;

$app
    ->get('/', BaseController::action(IndexController::class, 'index'))
    ->setName('index');

$app
    ->map(['GET', 'POST'], '/register', BaseController::action(IndexController::class, 'register'))
    ->setName('register');
$app
    ->get('/login', BaseController::action(IndexController::class, 'login'))
    ->setName('login');
