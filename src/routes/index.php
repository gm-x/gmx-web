<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\IndexController;

$app
    ->get('/', BaseController::action(IndexController::class, 'index'))
    ->setName('index');

include __DIR__ . DIRECTORY_SEPARATOR . 'user.php';

$app->group('/admin', function () {
    $this
        ->get('', BaseController::action(\GameX\Controllers\Admin\AdminController::class, 'index'))
        ->setName('admin_index')
        ->setArgument('permission', 'admin.*');

    $root = __DIR__ . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
    $this->group('/users', include  $root . 'users.php');
    $this->group('/roles', include $root . 'roles.php');
    $this->group('/servers', include $root . 'servers.php');
    $this->group('/players', include $root . 'players.php');
});

$app->group('/api', function () {
    $root = __DIR__ . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR;
    $this->group('/privileges', include  $root . 'privileges.php');
})->add(function (\Slim\Http\Request $request, \Slim\Http\Response $response, callable $next) use ($container) {
	if (!preg_match('/Bearer\s+(?P<token>.+)$/i', $request->getHeaderLine('Authorization'), $matches)) {
		throw new \Slim\Exception\SlimException($request, $response);
	}
	$config = $container->get('config');
	$data = \Firebase\JWT\JWT::decode($matches['token'], $config['secret'], ['HS256', 'HS512']);
	if (empty($data->server_id)) {
		throw new \Slim\Exception\SlimException($request, $response);
	}
	return $next($request->withAttribute('server_id', $data->server_id), $response);
});
