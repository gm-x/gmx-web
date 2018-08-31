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

$container['root'] = __DIR__ . DIRECTORY_SEPARATOR;

$container['config'] = function ($container) {
	return new \GameX\Core\Configuration\Config($container['root'] . DIRECTORY_SEPARATOR . 'config.json');
};

$container['db'] = function ($container) {
	/** @var \GameX\Core\Configuration\Config $config */
	$config = $container['config'];
    $capsule = new Capsule();
    $capsule->addConnection($config->getNode('db')->toArray());
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

$container['auth'] = function ($container) {
	$container['db'];
	$bootsrap = new \GameX\Core\Auth\SentinelBootstrapper();
	return $bootsrap->createSentinel();
};

$container['phpmig.adapter'] = function($container) {
    return new Adapter\Illuminate\Database($container['db'], 'migrations');
};

return $container;
