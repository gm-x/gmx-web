<?php

use \Phpmig\Adapter;
use \Illuminate\Database\Capsule\Manager as Capsule;
use \Pimple\Container;

$container = new Container();

// replace this with a better Phpmig\Adapter\AdapterInterface
$container['phpmig.adapter'] = new Adapter\File\Flat(__DIR__ . DIRECTORY_SEPARATOR . 'migrations/.migrations.log');

$container['phpmig.migrations_path'] = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';

// You can also provide an array of migration files
// $container['phpmig.migrations'] = array_merge(
//     glob('migrations_1/*.php'),
//     glob('migrations_2/*.php')
// );

$container['config'] = json_decode(file_get_contents(__DIR__ . '/config.json'), true);

$container['db'] = function ($c) {
    $capsule = new Capsule();
    $capsule->addConnection($c['config']['db']);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

$container['phpmig.adapter'] = function($c) {
    return new Adapter\Illuminate\Database($c['db'], 'migrations');
};

return $container;
