<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\IndexController;
use \GameX\Controllers\PunishmentsController;
use \GameX\Controllers\API\PrivilegesController;
use \GameX\Controllers\API\PlayersController;
use \GameX\Controllers\Admin\AdminController;

$authMiddleware = new \GameX\Core\Auth\AuthMiddleware($container);
$csrfMiddleware = new \GameX\Core\CSRF\Middleware($container->get('csrf'));

$app->group('', function () {
    /** @var \Slim\App $this */
    $this
        ->get('/', BaseController::action(IndexController::class, 'index'))
        ->setName('index');

    $this
        ->get('/punishments', BaseController::action(PunishmentsController::class, 'index'))
        ->setName('punishments');

    include __DIR__ . DIRECTORY_SEPARATOR . 'user.php';

    $modules = $this->getContainer()->get('modules');
    /** @var \GameX\Core\Module\ModuleInterface $module */
    foreach ($modules as $module) {
        $routes = $module->getRoutes();
        /** @var \GameX\Core\Module\ModuleRoute $route */
        foreach ($routes as $route) {
            $this
                ->map($route->getMethods(), $route->getRoute(), BaseController::action($route->getController(), $route->getAction()))
                ->setName($route->getName())
                ->setArgument('permission', $route->getPermission());
        }
    }
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

	$modules = $this->getContainer()->get('modules');
	/** @var \GameX\Core\Module\ModuleInterface $module */
	foreach ($modules as $module) {
		$routes = $module->getAdminRoutes();
		/** @var \GameX\Core\Module\ModuleRoute $route */
		foreach ($routes as $route) {
			$this
				->map($route->getMethods(), $route->getRoute(), BaseController::action($route->getController(), $route->getAction()))
				->setName($route->getName())
				->setArgument('permission', $route->getPermission());
		}
	}
})
    ->add($authMiddleware)
    ->add($csrfMiddleware);

$app->group('/api', function () {
    $this->post('/privileges', BaseController::action(PrivilegesController::class, 'index'));
    $this->post('/player', BaseController::action(PlayersController::class, 'player'));
    $this->post('/punish', BaseController::action(PlayersController::class, 'punish'));
})->add(function (\Slim\Http\Request $request, \Slim\Http\Response $response, callable $next) use ($container) {
	if (!preg_match('/Basic\s+(?P<token>.+)$/i', $request->getHeaderLine('Authorization'), $matches)) {
		throw new \GameX\Core\Exceptions\NotAllowedException($request, $response);
	}
	$config = $container->get('config');
	$token = base64_decode($matches['token']);
	if ($token === false) {
        throw new \GameX\Core\Exceptions\NotAllowedException($request, $response);
    }
    $token = trim($token, ':');
	$data = \Firebase\JWT\JWT::decode($token, $config['secret'], ['HS512']);
	if (empty($data->server_id)) {
		throw new \GameX\Core\Exceptions\NotAllowedException($request, $response);
	}
	return $next($request->withAttribute('server_id', $data->server_id), $response);
})->add(function (\Slim\Http\Request $request, \Slim\Http\Response $response, callable $next) {
    try {
        return $next($request, $response);
    } catch (\GameX\Core\Exceptions\ApiException $e) {
        return $response
            ->withJson([
                'success' => false,
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ],
            ]);
    } catch (\Exception $e) {
        return $response
            ->withJson([
                'success' => false,
                'error' => [
                    'code' => \GameX\Core\Exceptions\ApiException::ERROR_GENERIC,
                    'message' => 'Error',
                ],
            ]);
    }
});
