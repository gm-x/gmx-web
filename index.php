<?php
$array = [];

function pushArray($key, $value) {
    global $array;

    if(count($array) >= 2) {
        $array = array_slice($array, 0, 1);
    }
    $array[$key] = $value;
}

require __DIR__ . '/vendor/autoload.php';

$config = [
    'settings' => [
        'determineRouteBeforeAppMiddleware' => true,
        'displayErrorDetails' => true,
    ],
];

$config['config'] = json_decode(file_get_contents(__DIR__ . '/config.json'), true);

$app = new \Slim\App($config);

$container = $app->getContainer();
$container['root'] = __DIR__ . DIRECTORY_SEPARATOR;

include __DIR__ . '/src/dependencies.php';
include __DIR__ . '/src/middlewares.php';
include __DIR__ . '/src/routes.php';

$app->run();
