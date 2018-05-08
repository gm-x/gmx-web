<?php
$app->add(new RKA\Middleware\IpAddress(true));
//$app->add($container->get('csrf'));
$app->add(new \Slim\Middleware\Session($container['config']['session']));


