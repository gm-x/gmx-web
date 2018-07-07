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

$container['cache'] = function (\Psr\Container\ContainerInterface $container) {
	$driver = new \Stash\Driver\FileSystem([
		'path' => $container['root'] . 'runtime' . DIRECTORY_SEPARATOR . 'cache',
		'encoder' => 'Serializer'
	]);
	return new \Stash\Pool($driver);
};

$container['lang'] = function (\Psr\Container\ContainerInterface $container) {
    return new GameX\Core\Lang\Language(
        $container['root'] . DIRECTORY_SEPARATOR . 'languages',
        $container->get('session'),
        $container->get('request'),
        $container['config']['language']
    );
};

$container['db'] = function (\Psr\Container\ContainerInterface $container) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($container['config']['db']);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    $capsule->getConnection()->enableQueryLog();

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
    return new \GameX\Core\Forms\FormFactory($container->get('session'));
};

$container['view'] = function (\Psr\Container\ContainerInterface $container) {
	$view = new \Slim\Views\Twig($container->get('root') . 'templates', array_merge([
		'cache' => $container->get('root') . 'runtime' . DIRECTORY_SEPARATOR . 'twig_cache',
	], $container['config']['twig']));

	// Instantiate and add Slim specific extension
	$basePath = rtrim(str_ireplace('index.php', '', $container->get('request')->getUri()->getBasePath()), '/');
	$view->addExtension(new \Slim\Views\TwigExtension($container->get('router'), $basePath));
	$view->addExtension(new \GameX\Core\CSRF\Extension($container->get('csrf')));
	$view->addExtension(new \GameX\Core\Auth\ViewExtension($container->get('auth')));
	$view->addExtension(new \GameX\Core\Lang\ViewExtension($container->get('lang')));
	$view->addExtension(new \GameX\Core\AccessFlags\ViewExtension());
	$view->addExtension(new \GameX\Core\Twig_Dump());

	$view->getEnvironment()->addGlobal('flash_messages', $container->get('flash'));

	return $view;
};

$container['modules'] = function (\Psr\Container\ContainerInterface $container) {
	$modules = new \GameX\Core\Module\Module();
	$modules->addModule(new GameX\Modules\TestModule\Module());
	return $modules;
};

\GameX\Core\BaseModel::setContainer($container);
