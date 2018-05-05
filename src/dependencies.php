<?php

$container = $app->getContainer();
$container['root'] = __DIR__ . DIRECTORY_SEPARATOR;

$container['session'] = function ($c) {
    return new \SlimSession\Helper;
};

$container['view'] = function (\Psr\Container\ContainerInterface $container) {
    $view = new \Slim\Views\Twig($container->get('root') . 'templates', array_merge([
        'cache' => $container->get('root') . 'cache',
    ], $container['config']['twig']));

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container->get('request')->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container->get('router'), $basePath));
    $view->addExtension(new GameX\Core\Forms\FormExtension());

    return $view;
};

$container['db'] = function (\Psr\Container\ContainerInterface $container) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($container['config']['db']);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

$container['auth'] = function (\Psr\Container\ContainerInterface $container) {
    $container->get('db');
    $bootsrap = new \GameX\Core\Sentinel\SentinelBootstrapper($container->get('request'), $container->get('session'));
    return $bootsrap->createSentinel();
};

$container['mail'] = function (\Psr\Container\ContainerInterface $container) {
    $mailer = new \Tx\Mailer();
    $config = $container['config']['mailer'];
    return $mailer->setServer($config['host'], $config['port']);
};
