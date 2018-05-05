<?php

require __DIR__ . '/vendor/autoload.php';

$app = new \Slim\App;

$container = $app->getContainer();

$container['config'] = json_decode(file_get_contents(__DIR__ . '/config.json'), true);

$app->add(new \Slim\Middleware\Session($container['config']['session']));

$app->add(new RKA\Middleware\IpAddress(true));

$container['root'] = __DIR__ . DIRECTORY_SEPARATOR;

$container['session'] = function ($c) {
    return new \SlimSession\Helper;
};

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig($container->get('root') . 'templates', array_merge([
        'cache' => $container->get('root') . 'cache',
    ], $container['config']['twig']));

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container->get('request')->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container->get('router'), $basePath));
    $view->addExtension(new GameX\Core\Forms\FormExtension());

    return $view;
};

$container['db'] = function ($container) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($container['config']['db']);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

$container['auth'] = function ($container) {
    $container->get('db');
    $bootsrap = new \GameX\Core\Sentinel\SentinelBootstrapper($container->get('request'), $container->get('session'));
    return $bootsrap->createSentinel();
};

include __DIR__ . '/src/routes.php';

$app->run();
