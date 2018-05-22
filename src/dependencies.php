<?php
$container['session'] = function (\Psr\Container\ContainerInterface $container) {
    return new GameX\Core\Session\Session();
};

$container['flash'] = function (\Psr\Container\ContainerInterface $container) {
    return new \GameX\Core\FlashMessages($container->get('session'), 'flash_messages');
};

$container['csrf'] = function (\Psr\Container\ContainerInterface $container) {
    return new \GameX\Core\CSRF\Token($container->get('session'));
};

$container['view'] = function (\Psr\Container\ContainerInterface $container) {
    $view = new \Slim\Views\Twig($container->get('root') . 'templates', array_merge([
        'cache' => $container->get('root') . 'cache',
    ], $container['config']['twig']));

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container->get('request')->getUri()->getBasePath()), '/');
    $view->addExtension(new \Slim\Views\TwigExtension($container->get('router'), $basePath));
    $view->addExtension(new \GameX\Core\Forms\FormExtension());
    $view->addExtension(new \GameX\Core\CSRF\Extension($container->get('csrf')));
    $view->addExtension(new \GameX\Core\Pagination\Extention());
    $view->addExtension(new \GameX\Core\Auth\TwigExtention());

    return $view;
};

$container['db'] = function (\Psr\Container\ContainerInterface $container) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($container['config']['db']);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

$container['auth'] = function (\Psr\Container\ContainerInterface $container) {
    $container->get('db');
    $bootsrap = new \GameX\Core\Auth\SentinelBootstrapper($container->get('request'), $container->get('session'));
    return $bootsrap->createSentinel();
};

$container['mail'] = function (\Psr\Container\ContainerInterface $container) {
    return new \GameX\Core\Mail\MailHelper($container);
};

$container['log'] = function (\Psr\Container\ContainerInterface $container) {
	$log = new \Monolog\Logger('name');
	$logPath = $container['root'] . '/tmp.log';
	$log->pushHandler(new \Monolog\Handler\StreamHandler($logPath, \Monolog\Logger::DEBUG));

	return $log;
};

$container['form'] = function (\Psr\Container\ContainerInterface $container) {
    return new \GameX\Core\Forms\FormFactory($container);
};
