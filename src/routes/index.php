<?php
use \GameX\Middlewares\ApiTokenMiddleware;
use \GameX\Middlewares\ApiRequestMiddleware;

$authMiddleware = new \GameX\Middlewares\AuthMiddleware($app->getContainer());
$csrfMiddleware = new \GameX\Core\CSRF\Middleware($app->getContainer());
$securityMiddleware = new \GameX\Middlewares\SecurityMiddleware($app->getContainer());

$app->group('', \GameX\routes\MainRoutes::class)
    ->add($authMiddleware)
    ->add($csrfMiddleware)
    ->add($securityMiddleware);

$app->group('/admin', \GameX\routes\AdminRoutes::class)
    ->add($authMiddleware)
    ->add($csrfMiddleware)
    ->add($securityMiddleware);

$app->group('/api', \GameX\Routes\ApiRoutes::class)
    ->add(new ApiTokenMiddleware())
    ->add(new ApiRequestMiddleware($app->getContainer()));