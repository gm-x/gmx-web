<?php
require __DIR__ . '/vendor/autoload.php';

// TODO: Remove to helper class
function readFlags($flags) {
    $result = 0;
    for ($i = 0, $l = strlen($flags); $i < $l; $i++) {
        $f = ord($flags[$i]);
        if ($f >= 97 && $f <= 122) {
            $result |= (1 << ($f - 97));
        }
    }

    return $result;
}

function getFlags($flags) {
    $result = '';
    for ($i = 0; $i <= 32; $i++) {
        if ( ($flags  & ( 1 << $i ) ) > 0 ) {
            $result .= chr($i + 97);
        }
    }
    return $result;
}

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
include __DIR__ . '/src/routes/index.php';

$app->run();
