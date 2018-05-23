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

$container['lang'] = function (\Psr\Container\ContainerInterface $container) {
	$i18n = new \GameX\Core\Lang\I18n($container->get('session'), new \GameX\Core\Lang\LangProvider(), 'ru');
	$i18n->setPath($container['root'] . 'langs');
	return $i18n;
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
	$logPath = $container['root'] . 'runtime' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'log.log';
	$log->pushHandler(new \Monolog\Handler\RotatingFileHandler($logPath, 10, \Monolog\Logger::DEBUG));

	return $log;
};

$container['form'] = function (\Psr\Container\ContainerInterface $container) {
    return new \GameX\Core\Forms\FormFactory($container);
};

$container['view'] = function (\Psr\Container\ContainerInterface $container) {
	$view = new \Slim\Views\Twig($container->get('root') . 'templates', array_merge([
		'cache' => $container->get('root') . 'runtime' . DIRECTORY_SEPARATOR . 'twig_cache',
	], $container['config']['twig']));

	// Instantiate and add Slim specific extension
	$basePath = rtrim(str_ireplace('index.php', '', $container->get('request')->getUri()->getBasePath()), '/');
	$view->addExtension(new \Slim\Views\TwigExtension($container->get('router'), $basePath));
	$view->addExtension(new \GameX\Core\Forms\ViewExtension());
	$view->addExtension(new \GameX\Core\CSRF\Extension($container->get('csrf')));
	$view->addExtension(new \GameX\Core\Pagination\Extention());
	$view->addExtension(new \GameX\Core\Auth\ViewExtention($container->get('auth')));
	$view->addExtension(new \GameX\Core\Lang\ViewExtention($container->get('lang')));
	$view->addExtension(new \GameX\Core\AccessFlags\ViewExtension());

	return $view;
};

\GameX\Core\BaseModel::setContainer($container);
