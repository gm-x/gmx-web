<?php

require __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/src/config.php';
$app = new \Slim\App($config);

include __DIR__ . '/src/dependencies.php';
include __DIR__ . '/src/middlewares.php';
include __DIR__ . '/src/routes.php';

$app->run();
