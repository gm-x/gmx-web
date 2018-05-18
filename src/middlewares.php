<?php
//$app->add(new \GameX\Core\Auth\AuthMiddleware($container));
$app->add(new \RKA\Middleware\IpAddress(true));
$app->add(new \GameX\Core\CSRF\Middleware($container->get('csrf')));



