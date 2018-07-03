<?php
function render($template, array $data = []) {
	extract($data);
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . $template . '.php';
	return ob_get_clean();
}

function json($data) {
	header('Content-type:application/json;charset=utf-8');
	echo json_encode($data);
	die();
}

function getBaseUrl() {
	return rtrim(dirname($_SERVER['REQUEST_URI']), '/'	);
}

function downloadComposer($dir) {
	return file_put_contents($dir . DIRECTORY_SEPARATOR . 'composer.phar', file_get_contents('https://getcomposer.org/composer.phar')) !== false;
}

function extractComposer($dir) {
	$composerPhar = new Phar($dir . DIRECTORY_SEPARATOR .  'composer.phar');
	return $composerPhar->extractTo($dir);
}

function rrmdir($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (is_dir($dir."/".$object))
					rrmdir($dir."/".$object);
				else
					unlink($dir."/".$object);
			}
		}
		rmdir($dir);
	}
}

function composerInstall($baseDir) {
	$tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('GameX', true) . DIRECTORY_SEPARATOR;

	if (!is_dir($tempDir)) {
		if (!mkdir($tempDir, 0777, true)) {
			throw new Exception('Can\'t create folder ' . $tempDir);
		}
	}

	if (!downloadComposer($tempDir)) {
		throw new Exception('Can\'t download composer to ' . $tempDir);
	}
	if (!extractComposer($tempDir)) {
		throw new Exception('Can\'t download composer to ' . $tempDir);
	}
	require_once($tempDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

	chdir($baseDir);
//	https://getcomposer.org/doc/03-cli.md#composer-vendor-dir
	putenv('COMPOSER_HOME=' . $tempDir . '/vendor/bin/composer');
	putenv('COMPOSER_VENDOR_DIR=' . $baseDir . '/vendor');
	putenv('COMPOSER_BIN_DIR=' . $baseDir . '/vendor/bin');

	$input = new \Symfony\Component\Console\Input\ArrayInput(['command' => 'install']);
	$output = new \Symfony\Component\Console\Output\NullOutput();
	$application = new \Composer\Console\Application();
	$application->setAutoExit(false);
	$application->run($input, $output);

	rrmdir($tempDir);
}

function getBaseConfig() {
	return [
		'db' => [
			'driver' => 'mysql',
			'host' => '127.0.0.1',
			'port' => 3306,
			'database' => 'test',
			'username' => 'root',
			'password' => '',
			'charset' => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix' => ''
		],
		'session' => [
			'name' => 'sessid',
			'autorefresh' => true,
			'lifetime' => '1 hour'
		],
		'twig' => [
			'debug' => true,
			'auto_reload' => true
		],
		'mail' => [
			'from' => [
				'name' => 'test',
				'email' => 'test@example.com'
			],
			'transport' => [
				'type' => 'smtp',
				'host' => '127.0.0.1',
				'port' => 4651
			]
		],
		'secret' => 'secret_key'
	];
}

function checkDbConnection($config) {
	try {
		$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s', $config['host'], $config['port'], $config['name']);
		$dbh = new PDO($dsn, $config['user'], $config['pass']);
		$dbh = null;
		return true;
	} catch (PDOException $e) {
		return false;
	}
}

function runMigrations($container) {
	$output = new \Symfony\Component\Console\Output\NullOutput();
	$app = new \Phpmig\Api\PhpmigApplication($container, $output);

	$app->up();
}

function createUser($container, $email, $password) {
	/** @var \Cartalyst\Sentinel\Sentinel $auth */
	$auth = $container['auth'];

    $role = $auth->getRoleRepository()->createModel()->create([
        'name' => 'Admins',
        'slug' => 'admin',
        'permissions' => array_keys(\GameX\Controllers\Admin\RolesController::PERMISSIONS)
    ]);

    /** @var \GameX\Core\Auth\Models\UserModel $user */
	$user = $auth->register([
		'email'  => $email,
		'password' => $password,
	], true);

    $user->role()->associate($role)->save();
}

function getContainer($baseDir, $phpmig = false) {
    require $baseDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    $container = new \GameX\Core\Container();
    $container['config'] = json_decode(file_get_contents($baseDir . DIRECTORY_SEPARATOR . 'config.json'), true);
    if ($phpmig) {
        require $baseDir . DIRECTORY_SEPARATOR . 'phpmig.php';
    } else {
        require $baseDir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'dependencies.php';
    }

	return $container;
}
