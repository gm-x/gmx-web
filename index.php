<?php

require __DIR__ . '/vendor/autoload.php';

$app = new \Slim\App;

$container = $app->getContainer();


$container['root'] = __DIR__ . DIRECTORY_SEPARATOR;

$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig($c->get('root') . 'templates', [
        'cache' => $c->get('root') . 'cache',
        // DEBUG
        'debug' => true,
        'auto_reload' => true,

    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $c->get('request')->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($c->get('router'), $basePath));

    return $view;
};

include __DIR__ . '/src/routes.php';

$app->run();
