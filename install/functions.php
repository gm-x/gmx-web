<?php
function render($template, array $data = []) {
	extract($data);
	ob_start();
	include __DIR__ . DS . $template . '.php';
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

function checkPhpVersion() {
    return version_compare(PHP_VERSION, '5.6.0') >= 0;
}

function downloadComposer($dir) {
	return file_put_contents($dir . DS . 'composer.phar', file_get_contents('https://getcomposer.org/composer.phar')) !== false;
}

function extractComposer($dir) {
	$composerPhar = new Phar($dir . DS .  'composer.phar');
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
	$tempDir = sys_get_temp_dir() . DS . uniqid('GameX', true) . DS;

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
	require_once($tempDir . DS . 'vendor' . DS . 'autoload.php');

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
			'enabled' => false,
			'from' => [
				'name' => 'test',
				'email' => 'test@example.com'
			],
			'transport' => [
				'type' => 'none'
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

function createUser($container, $login, $email, $password) {
	/** @var \Cartalyst\Sentinel\Sentinel $auth */
	$auth = $container['auth'];

	$permissions = array_fill_keys(
        array_keys(\GameX\Controllers\Admin\RolesController::PERMISSIONS),
        true
    );

    $role = $auth->getRoleRepository()->createModel()->create([
        'name' => 'Admins',
        'slug' => 'admin',
        'permissions' => $permissions
    ]);

    /** @var \GameX\Core\Auth\Models\UserModel $user */
	$user = $auth->register([
		'login'  => $login,
		'email'  => $email,
		'password' => $password,
	], true);

    $user->role()->associate($role)->save();
}

function getContainer($baseDir, $phpmig = false) {
    require $baseDir . DS . 'vendor' . DS . 'autoload.php';
    $container = new \GameX\Core\Container();
    $container['config'] = json_decode(file_get_contents($baseDir . DS . 'config.json'), true);
    if ($phpmig) {
        require $baseDir . DS . 'phpmig.php';
    } else {
        require $baseDir . DS . 'src' . DS . 'dependencies.php';
    }

	return $container;
}
