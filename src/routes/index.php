<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\IndexController;
use \GameX\Controllers\PunishmentsController;
use \GameX\Controllers\API\PrivilegesController;
use \GameX\Controllers\API\PlayersController;
use \GameX\Controllers\Admin\AdminController;

$authMiddleware = new \GameX\Core\Auth\AuthMiddleware($app->getContainer());
$csrfMiddleware = new \GameX\Core\CSRF\Middleware($app->getContainer()->get('csrf'));

$app->group('', function () {
    /** @var \Slim\App $this */
    $this
        ->get('/', BaseController::action(IndexController::class, 'index'))
        ->setName('index');

    $this
        ->get('/lang', BaseController::action(IndexController::class, 'language'))
        ->setName('language');

    $this
        ->get('/punishments', BaseController::action(PunishmentsController::class, 'index'))
        ->setName('punishments');

    include __DIR__ . DIRECTORY_SEPARATOR . 'user.php';
    include __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';

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
})->add(function (\Slim\Http\Request $request, \Slim\Http\Response $response, callable $next) {
    try {
        if (!preg_match('/Basic\s+(?P<token>.+?)$/i', $request->getHeaderLine('Authorization'), $matches)) {
            throw new \GameX\Core\Exceptions\NotAllowedException();
        }
    
        $token = base64_decode($matches['token']);
        if (!$token) {
            throw new \GameX\Core\Exceptions\NotAllowedException();
        }
        
        list ($token) = explode(':', $token);
        if (empty($token)) {
            throw new \GameX\Core\Exceptions\NotAllowedException();
        }
    
        $server = \GameX\Models\Server::where('token', $token)->first();
        if (!$server || $server->ip !== $request->getAttribute('ip_address')) {
            throw new \GameX\Core\Exceptions\NotAllowedException();
        }
        return $next($request->withAttribute('server_id', $server->id), $response);
    } catch (\GameX\Core\Exceptions\NotAllowedException $e) {
        return $response
            ->withJson([
                'success' => false,
                'error' => [
                    'code' => 403,
                    'message' => 'Bad token',
                ],
            ]);
    }
})->add(function (\Slim\Http\Request $request, \Slim\Http\Response $response, callable $next) use ($app) {
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
        $app->getContainer()->get('log')->error((string)$e);
        return $response
            ->withJson([
                'success' => false,
                'error' => [
                    'code' => \GameX\Core\Exceptions\ApiException::ERROR_GENERIC,
                    'message' => 'Server Error',
                ],
            ]);
    }
});
