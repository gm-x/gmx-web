<?php
$app->add(new \GameX\Core\Auth\AuthMiddleware($container->get('auth')));
$app->add(new RKA\Middleware\IpAddress(true));
//$app->add(new \Slim\Middleware\Session($container['config']['session']));
//$app->add($container->get('csrf'));
//$app->add(new \GameX\Core\Session($container['config']['session']));


