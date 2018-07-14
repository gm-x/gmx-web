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

function checkPhpVersion($version) {
    return version_compare(PHP_VERSION, $version) >= 0;
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
			if ($object != '.' && $object != '..') {
				if (is_dir($dir . DS . $object))
					rrmdir($dir . DS . $object);
				else
					unlink($dir . DS . $object);
			}
		}
		rmdir($dir);
	}
}

function composerInstall() {
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
	require_once($tempDir . 'vendor' . DS . 'autoload.php');

	chdir(BASE_DIR);
//	https://getcomposer.org/doc/03-cli.md#composer-vendor-dir
	putenv('COMPOSER_HOME=' . $tempDir . 'vendor/bin/composer');
	putenv('COMPOSER_VENDOR_DIR=' . BASE_DIR . 'vendor');
	putenv('COMPOSER_BIN_DIR=' . BASE_DIR . 'vendor/bin');

	$input = new \Symfony\Component\Console\Input\ArrayInput(['command' => 'install']);
	$output = new \Symfony\Component\Console\Output\NullOutput();
	$application = new \Composer\Console\Application();
	$application->setAutoExit(false);
	$application->run($input, $output);

	rrmdir($tempDir);
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

function generateSecretKey() {
	return bin2hex(random_bytes(32));
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

function getContainer($phpmig = false) {
    require BASE_DIR . 'vendor' . DS . 'autoload.php';
    $container = new \GameX\Core\Container();
    $container['root'] = BASE_DIR;
    if ($phpmig) {
        require BASE_DIR . 'phpmig.php';
    } else {
        require BASE_DIR . 'src' . DS . 'dependencies.php';
    }

	return $container;
}

function logException(\Exception $e) {
	file_put_contents(__DIR__ . DS . 'install.log', (string) $e . PHP_EOL . PHP_EOL, FILE_APPEND);
}

function checkDirectories(array $directories) {
    foreach ($directories as $directory) {
        if (is_dir($directory)) {
            if (!is_writable($directory) && !chmod($directory, 0755)) {
                throw new Exception('Can\'t write to directory ' . $directory);
            }
        } else {
            if (!mkdir($directory, 0755, true)) {
                throw new Exception('Can\'t create directory ' . $directory);
            }
        }
        
    }
}
