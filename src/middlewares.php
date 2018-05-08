<?php
$app->add(new \GameX\Core\Auth\AuthMiddleware($container->get('auth')));
$app->add(new RKA\Middleware\IpAddress(true));
$app->add($container->get('csrf'));
$app->add(new \Slim\Middleware\Session($container['config']['session']));


