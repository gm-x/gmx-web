<?php
$app->add(new \Slim\Middleware\Session($container['config']['session']));

$app->add(new RKA\Middleware\IpAddress(true));