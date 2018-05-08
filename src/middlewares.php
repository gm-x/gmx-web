<?php
$app->add(new \GameX\Core\Auth\AuthMiddleware($container->get('auth')));
$app->add(new RKA\Middleware\IpAddress(true));
// TODO: Fix problems with CSRF. Temporally disabled
//$app->add($container->get('csrf'));
$app->add(new \Slim\Middleware\Session($container['config']['session']));


