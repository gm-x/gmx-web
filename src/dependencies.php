<?php
use \Psr\Container\ContainerInterface;

$container['config'] = function (ContainerInterface $container) {
    return new \GameX\Core\Configuration\Config($container->get('root') . '/config.json');
};

$container['session'] = function (ContainerInterface $container) {
    return new \GameX\Core\Session\Session();
};

$container['flash'] = function (ContainerInterface $container) {
    return new \GameX\Core\FlashMessages($container->get('session'), 'flash_messages');
};

$container['csrf'] = function (ContainerInterface $container) {
    return new \GameX\Core\CSRF\Token($container->get('session'));
};

$container['cache'] = function (ContainerInterface $container) {
	$driver = new \Stash\Driver\FileSystem([
		'path' => $container['root'] . 'runtime' . DIRECTORY_SEPARATOR . 'cache',
		'encoder' => 'Serializer'
	]);
	return new \Stash\Pool($driver);
};

$container['lang'] = function (ContainerInterface $container) {
    /** @var GameX\Core\Configuration\Node $config */
    $config = $container->get('config')->getNode('language');

    $loader = new \GameX\Core\Lang\Loaders\JSONLoader($container['root'] . DIRECTORY_SEPARATOR . 'languages');
    $provider = new \GameX\Core\Lang\Providers\SlimProvider($container->get('request'));
    return new \GameX\Core\Lang\Language(
        $loader, $provider,
        $config->getNode('list')->toArray(),
        $config->get('default')
    );
};

$container['db'] = function (ContainerInterface $container) {
    /** @var GameX\Core\Configuration\Config $config */
    $config = $container->get('config');

    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($config->getNode('db')->toArray());

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    if ($config->getNode('log')->get('queries', false)) {
        $capsule->getConnection()->enableQueryLog();
    }

    return $capsule;
};

$container['permissions'] = function () {
    return new \GameX\Core\Auth\Permissions\Manager();
};

$container['auth'] = function (\Psr\Container\ContainerInterface $container) {
    $container->get('db');
    $bootsrap = new \GameX\Core\Auth\SentinelBootstrapper(
        $container->get('request'),
        $container->get('session'),
        $container->get('permissions')
    );
    return $bootsrap->createSentinel();
};

$container['mail'] = function (ContainerInterface $container) {
    /** @var GameX\Core\Configuration\Config $config */
    $config = $container->get('config');

//    return new \GameX\Core\Mail\Helpers\SwiftMailer($container->get('view'), $config->get('mail'));
    return new \GameX\Core\Mail\Helpers\MailHelper($container->get('view'), $config->getNode('mail'));
};

$container['log'] = function (ContainerInterface $container) {
	$log = new \Monolog\Logger('name');
	$logPath = $container['root'] . 'runtime' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'log.log';
	$log->pushHandler(new \Monolog\Handler\RotatingFileHandler($logPath, 10, \Monolog\Logger::DEBUG));

	return $log;
};

$container['form'] = function (ContainerInterface $container) {
    return new \GameX\Core\Forms\FormFactory($container->get('session'), $container->get('lang'));
};

$container['view'] = function (ContainerInterface $container) {
    /** @var GameX\Core\Configuration\Config $config */
    $config = $container->get('config');

    $settings = $config->getNode('view')->toArray();
    $settings['cache'] = $container->get('root') . 'runtime' . DIRECTORY_SEPARATOR . 'twig_cache';

	$view = new \Slim\Views\Twig($container->get('root') . 'templates', $settings);

	/** @var \Psr\Http\Message\UriInterface $uri */
	$uri = $container->get('request')->getUri();

	// Instantiate and add Slim specific extension
	$basePath = rtrim(str_ireplace('index.php', '', $uri->getBasePath()), '/');
	$view->addExtension(new \Slim\Views\TwigExtension($container->get('router'), $basePath));
	$view->addExtension(new \GameX\Core\CSRF\Extension($container->get('csrf')));
	$view->addExtension(new \GameX\Core\Auth\ViewExtension($container->get('auth')));
	$view->addExtension(new \GameX\Core\Lang\Extension\ViewExtension($container->get('lang')));
	$view->addExtension(new \GameX\Core\AccessFlags\ViewExtension());
	$view->addExtension(new \GameX\Core\Twig_Dump());

	$view->getEnvironment()->addGlobal('flash_messages', $container->get('flash'));
	$view->getEnvironment()->addGlobal('currentUri', (string)$uri->getPath());
	$view->getEnvironment()->addGlobal('title', $config->getNode('main')->get('title'));

	return $view;
};

$container['modules'] = function (ContainerInterface $container) {
	$modules = new \GameX\Core\Module\Module();
//	$modules->addModule(new \GameX\Modules\TestModule\Module());
	return $modules;
};

\GameX\Core\BaseModel::setContainer($container);
\GameX\Core\BaseForm::setContainer($container);
\GameX\Core\Auth\Middlewares\BaseMiddleware::setContainer($container);
date_default_timezone_set('UTC');
