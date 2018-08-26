<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\IndexController;
use \GameX\Controllers\PunishmentsController;
use \GameX\Controllers\API\ServerController;
use \GameX\Controllers\API\PlayersController;
use \GameX\Controllers\API\PunishController;
use \GameX\Controllers\Admin\AdminController;
use \GameX\Core\Auth\Permissions\Manager;
use \GameX\Core\Auth\Middlewares\HasAccessToGroup;
use \GameX\Core\Auth\Middlewares\HasAccessToPermission;
use \GameX\Middlewares\ApiTokenMiddleware;
use \GameX\Middlewares\ApiRequestMiddleware;

$authMiddleware = new \GameX\Core\Auth\Middlewares\AuthMiddleware($app->getContainer());
$csrfMiddleware = new \GameX\Core\CSRF\Middleware($app->getContainer()->get('csrf'));

$app->group('', function () {
    /** @var \Slim\App $this */
    $this
        ->get('/', BaseController::action(IndexController::class, 'index'))
        ->setName('index')
        ->add(new HasAccessToPermission('user', 'index'));

    $this
        ->get('/lang', BaseController::action(IndexController::class, 'language'))
        ->setName('language');

    $this
        ->get('/punishments', BaseController::action(PunishmentsController::class, 'index'))
        ->setName('punishments')
        ->add(new HasAccessToPermission('user', 'punishment', Manager::ACCESS_LIST));

    include __DIR__ . DIRECTORY_SEPARATOR . 'user.php';
    include __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';

//    $modules = $this->getContainer()->get('modules');
//    /** @var \GameX\Core\Module\ModuleInterface $module */
//    foreach ($modules as $module) {
//        $routes = $module->getRoutes();
//        /** @var \GameX\Core\Module\ModuleRoute $route */
//        foreach ($routes as $route) {
//            $this
//                ->map($route->getMethods(), $route->getRoute(), BaseController::action($route->getController(), $route->getAction()))
//                ->setName($route->getName())
//                ->setArgument('permission', $route->getPermission());
//        }
//    }
})
    ->add($authMiddleware)
    ->add($csrfMiddleware);

$app->group('/admin', function () {
	/** @var \Slim\App $this */
    $this
        ->get('', BaseController::action(AdminController::class, 'index'))
        ->setName('admin_index')
        ->setArgument('permission', 'admin.*');

    $root = __DIR__ . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
    $this->group('/preferences', include  $root . 'preferences.php');
    $this->group('/users', include  $root . 'users.php');
    $this->group('/roles', include $root . 'roles.php');
    $this->group('/servers', include $root . 'servers.php');
    $this->group('/players', include $root . 'players.php');

//	$modules = $this->getContainer()->get('modules');
//	/** @var \GameX\Core\Module\ModuleInterface $module */
//	foreach ($modules as $module) {
//		$routes = $module->getAdminRoutes();
//		/** @var \GameX\Core\Module\ModuleRoute $route */
//		foreach ($routes as $route) {
//			$this
//				->map($route->getMethods(), $route->getRoute(), BaseController::action($route->getController(), $route->getAction()))
//				->setName($route->getName())
//				->setArgument('permission', $route->getPermission());
//		}
//	}
})
    ->add($authMiddleware)
    ->add($csrfMiddleware);

$app->group('/api', function () {
    $this->post('/info', BaseController::action(ServerController::class, 'index'));
    $this->post('/player', BaseController::action(PlayersController::class, 'index'));
    $this->post('/punish', BaseController::action(PunishController::class, 'index'));
    $this->post('/punish/immediately', BaseController::action(PunishController::class, 'immediately'));
})
    ->add(new ApiTokenMiddleware())
    ->add(new ApiRequestMiddleware($app->getContainer()->get('log')));
