<?php

require __DIR__ . '/vendor/autoload.php';

$config['config'] = json_decode(file_get_contents(__DIR__ . '/config.json'), true);
$app = new \Slim\App($config);

$container = $app->getContainer();
$container['root'] = __DIR__ . DIRECTORY_SEPARATOR;

include __DIR__ . '/src/dependencies.php';
include __DIR__ . '/src/middlewares.php';
include __DIR__ . '/src/routes.php';

$app->run();
