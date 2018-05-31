<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\IndexController;

$app
    ->get('/', BaseController::action(IndexController::class, 'index'))
    ->setName('index');

include __DIR__ . DIRECTORY_SEPARATOR . 'user.php';

$app->group('/admin', function () {
    $this
        ->get('', BaseController::action(\GameX\Controllers\Admin\AdminController::class, 'index'))
        ->setName('admin_index')
        ->setArgument('permission', 'admin.*');

    $root = __DIR__ . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
    $this->group('/users', include  $root . 'users.php');
    $this->group('/roles', include $root . 'roles.php');
    $this->group('/servers', include $root . 'servers.php');
    $this->group('/players', include $root . 'players.php');
});

$app->group('/api', function () {
    $root = __DIR__ . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR;
    $this->group('/privileges', include  $root . 'privileges.php');
});
