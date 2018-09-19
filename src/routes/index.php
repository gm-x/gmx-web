<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\IndexController;
use \GameX\Controllers\PunishmentsController;
use \GameX\Controllers\Admin\AdminController;
use \GameX\Middlewares\ApiTokenMiddleware;
use \GameX\Middlewares\ApiRequestMiddleware;
use \GameX\Core\Auth\Permissions;

$authMiddleware = new \GameX\Middlewares\AuthMiddleware($app->getContainer());
$csrfMiddleware = new \GameX\Core\CSRF\Middleware($app->getContainer()->get('csrf'));

$app->group('', function () {
    /** @var \Slim\App $this */
    $this
        ->get('/', BaseController::action(IndexController::class, 'index'))
        ->setName('index');

    $this
        ->post('/lang', BaseController::action(IndexController::class, 'language'))
        ->setName('language')
        ->setArgument('csrf_skip', true);

    $this
        ->get('/punishments', BaseController::action(PunishmentsController::class, 'index'))
        ->setName('punishments');

    include __DIR__ . DIRECTORY_SEPARATOR . 'user.php';
    include __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';
})
    ->add($authMiddleware)
    ->add($csrfMiddleware);

$app->group('/admin', function () {
	/** @var \Slim\App $this */

    /** @var Permissions $permissions */
    $permissions = $this->getContainer()->get('permissions');

    $this
        ->get('', BaseController::action(AdminController::class, 'index'))
        ->setName('admin_index')
        ->add($permissions->hasAccessToGroupMiddleware('admin'));

    $root = __DIR__ . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
    $this->group('/preferences', include  $root . 'preferences.php');
    $this->group('/users', include  $root . 'users.php');
    $this->group('/roles', include $root . 'roles.php');
    $this->group('/servers', include $root . 'servers.php');
    $this->group('/players', include $root . 'players.php');
})
    ->add($authMiddleware)
    ->add($csrfMiddleware);

$app->group('/api', function () {
    include __DIR__ . DIRECTORY_SEPARATOR . 'api.php';
})
    ->add(new ApiTokenMiddleware())
    ->add(new ApiRequestMiddleware($app->getContainer()->get('log')));
