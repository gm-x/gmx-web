<?php
function render($template, array $data = []) {
	extract($data);
	ob_start();
	include __DIR__ . DS . $template . '.php';
	return ob_get_clean();
}

function json($status, $message = '') {
	header('Content-type:application/json;charset=utf-8', true, 200);
	$data = [
	    'status' => (bool) $status
    ];
	if (!$status) {
	    $data['message'] = (string) $message;
    }
	echo json_encode($data);
	die();
}

function getBaseUrl() {
    $url = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    $url = str_replace('\\', '/', $url);
	return rtrim(dirname($url), '/');
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
	$tempDir = BASE_DIR  . 'runtime' . DS . 'install' . DS;

	if (is_dir($tempDir)) {
		clearDir($tempDir);
	} else if (!mkdir($tempDir, 0777, true)) {
		throw new Exception('Can\'t create folder ' . $tempDir);
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
	$output = new \Symfony\Component\Console\Output\BufferedOutput();
	$application = new \Composer\Console\Application();
	$application->setAutoExit(false);
	if ($application->run($input, $output) !== 0) {
        logMessage($output->fetch());
        throw new Exception('Can\'t install composer dependencies');
    }

	rrmdir($tempDir);
}

function checkDbConnection($config) {
    if (empty($config['host']) || empty($config['port']) || empty($config['name']) || empty($config['user'])) {
        throw new Exception('Bad connection params');
    }

    if ($config['engine'] === 'postgresql') {
        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $config['host'], $config['port'], $config['name']);
    } else {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s', $config['host'], $config['port'], $config['name']);
    }

    $dbh = new PDO($dsn, $config['user'], $config['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $dbh = null;
}

function checkAdminCredentials($config) {
    if (empty($config['login']) || empty($config['email']) || empty($config['pass'])) {
        throw new Exception('Bad credentials for admin');
    }
    
    if (filter_var($config['email'], FILTER_VALIDATE_EMAIL) === false) {
        throw new Exception('Bad credentials for admin');
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
    $defaultProvider = new \Slim\DefaultServicesProvider();
    $defaultProvider->register($container);
    
    $authHelper = new \GameX\Core\Auth\Helpers\AuthHelper($container);
    
    $user = $authHelper->registerUser($login, $email, $password, true);
	
	/** @var \GameX\Core\Configuration\Config $config */
	$config = $container['config'];
	$config->getNode('permissions')->set('root_user', $user->id);
	$config->save();
}

function getContainer($phpmig = false) {
    require BASE_DIR . 'vendor' . DS . 'autoload.php';
    $container = new \GameX\Core\Container();
    $container['root'] = BASE_DIR;
    if ($phpmig) {
        require BASE_DIR . 'phpmig.php';
    } else {
        $configProvider = new \GameX\Core\Configuration\Providers\PHPProvider(BASE_DIR . DS . 'config.php');
        $config = new \GameX\Core\Configuration\Config($configProvider);
        $container->register(new \GameX\Core\DependencyProvider($config));
        \GameX\Core\BaseModel::setContainer($container);
        \GameX\Core\BaseForm::setContainer($container);
        date_default_timezone_set('UTC');
    }

	return $container;
}

function logMessage($message) {
	file_put_contents(__DIR__ . DS . 'install.log', $message . PHP_EOL . PHP_EOL, FILE_APPEND);
}

function logError($e) {
    logMessage('Error #' . $e['file'] . ':' . $e['line'] . ' - ' . $e['message']);
}

function logException(\Exception $e) {
	logMessage((string) $e);
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

function cronjobExists($command){
    $cronjob_exists = false;
    exec('crontab -l', $crontab);
    if (isset($crontab) && is_array($crontab)) {

        $crontab = array_flip($crontab);

        if (isset($crontab[$command])) {
            $cronjob_exists=true;
        }
    }
    return $cronjob_exists;
}

function cronjobAppend($command){
    if (!empty($command) && !cronjobExists($command)) {
		exec('echo -e "`crontab -l`\n'.$command.'" | crontab -', $output);
		return true;
    }

    return false;
}

function clearDir($path) {
    foreach (new \RecursiveIteratorIterator(
                 new \RecursiveDirectoryIterator($path),
                 \RecursiveIteratorIterator::LEAVES_ONLY) as $file
    ) {
        if ($file->isFile()) {
            @unlink($file->getPathname());
        }
    }
}
