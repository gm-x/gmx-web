<?php
use \GameX\Core\BaseController;
use \GameX\Controllers\IndexController;
use \GameX\Controllers\PunishmentsController;
use \GameX\Controllers\API\PrivilegesController;
use \GameX\Controllers\API\PlayersController;
use \GameX\Controllers\Admin\AdminController;

$app
    ->get('/', BaseController::action(IndexController::class, 'index'))
    ->setName('index');

$app
	->get('/punishments', BaseController::action(PunishmentsController::class, 'index'))
	->setName('punishments');

include __DIR__ . DIRECTORY_SEPARATOR . 'user.php';

$app->group('/admin', function () {
    $this
        ->get('', BaseController::action(AdminController::class, 'index'))
        ->setName('admin_index')
        ->setArgument('permission', 'admin.*');

    $root = __DIR__ . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
    $this->group('/users', include  $root . 'users.php');
    $this->group('/roles', include $root . 'roles.php');
    $this->group('/servers', include $root . 'servers.php');
    $this->group('/players', include $root . 'players.php');
});

$app->group('/api', function () {
    $this->get('/privileges', BaseController::action(PrivilegesController::class, 'index'));
    $this->get('/players', BaseController::action(PlayersController::class, 'index'));
    $this->get('/Punish', BaseController::action(PlayersController::class, 'punish'));
})->add(function (\Slim\Http\Request $request, \Slim\Http\Response $response, callable $next) use ($container) {
	if (!preg_match('/Basic\s+(?P<token>.+)$/i', $request->getHeaderLine('Authorization'), $matches)) {
		throw new \Slim\Exception\SlimException($request, $response);
	}
	$config = $container->get('config');
	$token = base64_decode($matches['token']);
	if ($token === false) {
        throw new \Slim\Exception\SlimException($request, $response);
    }
    $token = trim($token, ':');
	$data = \Firebase\JWT\JWT::decode($token, $config['secret'], ['HS512']);
	if (empty($data->server_id)) {
		throw new \Slim\Exception\SlimException($request, $response);
	}
	return $next($request->withAttribute('server_id', $data->server_id), $response);
});
