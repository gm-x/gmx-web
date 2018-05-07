<?php
$app->add(new \Slim\Middleware\Session($container['config']['session']));
$app->add($container->get('csrf'));
$app->add(new RKA\Middleware\IpAddress(true));
