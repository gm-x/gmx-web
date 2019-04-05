<?php

use \Phpmig\Adapter;
use \Illuminate\Database\Capsule\Manager as Capsule;
use \Pimple\Container;

$container = new Container();

$container['phpmig.migrations_path'] = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';

// You can also provide an array of migration files
// $container['phpmig.migrations'] = array_merge(
//     glob('migrations_1/*.php'),
//     glob('migrations_2/*.php')
// );

$container['root'] = __DIR__ . DIRECTORY_SEPARATOR;

$container['config'] = function ($container) {
    $provider = new \GameX\Core\Configuration\Providers\PHPProvider($container['root'] . '/config.php');
    return new \GameX\Core\Configuration\Config($provider);
};
$container['db'] = function ($container) {
	/** @var \GameX\Core\Configuration\Config $config */
	$config = $container['config'];
    $capsule = new Capsule();
    $capsule->addConnection($config->getNode('db')->toArray());
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    
    \Illuminate\Database\Schema\Builder::defaultStringLength(191);

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
