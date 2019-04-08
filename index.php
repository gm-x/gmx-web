<?php
function redirectToInstall() {
    die('Need to be installed first');
}

if (!is_file(__DIR__ . '/vendor/autoload.php')) {
    redirectToInstall();
}

require __DIR__ . '/vendor/autoload.php';

try {
    $configProvider = new \GameX\Core\Configuration\Providers\PHPProvider(__DIR__ . '/config.php');
    $config = new \GameX\Core\Configuration\Config($configProvider);
} catch (\GameX\Core\Configuration\Exceptions\CantLoadException $e) {
    die('Can\'t load configuration file');
}

$settings = [
    'determineRouteBeforeAppMiddleware' => true,
];

if ($config->getNode('debug')->get('pretty')) {
    $settings['displayErrorDetails'] = true;
    $settings['debug'] = true;
}

if (!$config->getNode('debug')->get('routes')) {
    $settings['routerCacheFile'] = __DIR__ . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'routes.php';
    $settings['debug'] = true;
}

$container = new \Slim\Container([
	'settings' => $settings,
	'root' => __DIR__ . DIRECTORY_SEPARATOR,
]);

\GameX\Core\BaseModel::setContainer($container);
\GameX\Core\BaseForm::setContainer($container);
\GameX\Core\Utils::setContainer($container);
date_default_timezone_set('UTC');

$container['foundHandler'] = function() {
    return new \Slim\Handlers\Strategies\RequestResponseArgs();
};

$app = new \Slim\App($container);

if ($config->getNode('debug')->get('exceptions')) {
    $whoopsGuard = new \Zeuxisoo\Whoops\Provider\Slim\WhoopsGuard();
    $whoopsGuard->setApp($app);
    $whoopsGuard->setRequest($container['request']);
    $whoopsGuard->install();
} else {
    $errorHandler = function (\Slim\Container $container) {
        return function (\Slim\Http\Request $request, \Slim\Http\Response $response, $e) use ($container) {
            /** @var \Slim\Views\Twig $view */
            $view = $container->get('view');
            if ($e instanceof \GameX\Core\Exceptions\NotAllowedException) {
                return $view->render($response->withStatus(403), 'errors/403.twig');
            } elseif ($e instanceof \Slim\Exception\NotFoundException) {
                return $view->render($response->withStatus(404), 'errors/404.twig');
            } elseif ($e instanceof \Slim\Exception\MethodNotAllowedException) {
                return $view->render($response->withStatus(405), 'errors/405.twig');
            } else {
                $container->get('log')->exception($e);
                return $view->render($response->withStatus(500), 'errors/500.twig');
            }
        };
    };

    $notFoundHandler = function (\Slim\Container $container) {
        return function (\Slim\Http\Request $request, \Slim\Http\Response $response) use ($container) {
            if ($request->getMediaType() === 'application/json') {
                return $response->withStatus(404)->withJson([
                    "success" => false
                ]);
            } else {
                return $container['view']->render($response->withStatus(404), 'errors/404.twig');
            }
        };
    };

    $container['errorHandler'] = $errorHandler;
    $container['phpErrorHandler'] = $errorHandler;
    $container['notFoundHandler'] = $notFoundHandler;

    set_exception_handler(function ($e) use ($app) {
        if ($e instanceof \GameX\Core\Configuration\Exceptions\CantLoadException) {
            redirectToInstall();
        } else {
            $container = $app->getContainer();
            /** @var \Slim\Views\Twig $view */
            $view = $container->get('view');
            $container->get('log')->exception($e);
            $response = $view->render($container->get('response'), 'errors/500.twig')->withStatus(500);
            $app->respond($response);
        }
    });
}

$container->register(new \GameX\Core\DependencyProvider($config));
include __DIR__ . '/src/middlewares.php';
include __DIR__ . '/src/routes/index.php';

//set_error_handler(function ($errno, $error, $file, $line) use ($container) {
//    $container->get('log')->error("#$errno: $error in $file:$line");
//}, E_ALL);

$app->run();
