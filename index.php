<?php
if (!is_file(__DIR__ . '/vendor/autoload.php')) {
    $url = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    $url = str_replace('\\', '/', $url);
    $baseUrl =  rtrim(dirname($url), '/');
    
    header('Location: ' . $baseUrl . '/install',true,302);
    die();
}

require __DIR__ . '/vendor/autoload.php';

$container = new \Slim\Container([
	'settings' => [
		'determineRouteBeforeAppMiddleware' => true,
		'displayErrorDetails' => true,
	],
	'root' => __DIR__ . DIRECTORY_SEPARATOR
]);

$errorHandler = function ($c) {
	return function (\Slim\Http\Request $request, \Slim\Http\Response $response, $e) use ($c) {
	    $c['log']->error((string)$e);

        /** @var \Slim\Views\Twig $view */
        $view = $c->get('view');
	    if ($e instanceof \GameX\Core\Exceptions\NotAllowedException) {
            return $view->render($response->withStatus(403), 'errors/403.twig');
        } elseif ($e instanceof \Slim\Exception\NotFoundException) {
            return $view->render($response->withStatus(404), 'errors/404.twig');
        } elseif ($e instanceof \Slim\Exception\MethodNotAllowedException) {
            return $view->render($response->withStatus(405), 'errors/405.twig');
        } else {
            return $view->render($response->withStatus(500), 'errors/500.twig');
        }
	};
};

$container['errorHandler'] = $errorHandler;
$container['phpErrorHandler'] = $errorHandler;

$app = new \Slim\App($container);

include __DIR__ . '/src/dependencies.php';
include __DIR__ . '/src/middlewares.php';
include __DIR__ . '/src/routes/index.php';

/** @var \Monolog\Logger $logger */
$logger = $container->get('log');

//set_error_handler(function ($errno, $error, $file, $line) use ($logger) {
//    $logger->error("#$errno: $error in $file:$line");
//}, E_ALL);

$app->run();
