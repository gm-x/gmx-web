<?php

require __DIR__ . '/vendor/autoload.php';

$app = new \Slim\App;

$container = $app->getContainer();


$container['root'] = __DIR__ . DIRECTORY_SEPARATOR;

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig($container->get('root') . 'templates', [
        'cache' => $container->get('root') . 'cache',
        // DEBUG
        'debug' => true,
        'auto_reload' => true,

    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container->get('request')->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container->get('router'), $basePath));

    return $view;
};

$container['db'] = function ($container) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection([
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'database' => 'test',
        'username' => 'root',
        'password' => '',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
    ]);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

include __DIR__ . '/src/routes.php';

$app->run();
