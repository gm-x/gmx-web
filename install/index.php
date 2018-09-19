<?php
define('DS', DIRECTORY_SEPARATOR);
define('BASE_DIR', dirname(__DIR__) . DS);

include __DIR__ . DS . 'functions.php';

//if (file_exists(BASE_DIR . 'config.json')) {
//    header("Location: ".getBaseUrl());
//}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	echo render('template', [
		'baseUrl' => getBaseUrl()
	]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$step = isset($_GET['step']) ? $_GET['step'] : null;
	switch ($step) {
        case 'checks': {
            try {
                if (!checkPhpVersion('5.6.0')) {
                    throw new Exception('PHP must be 5.6.0 or higher');
                }
                checkDirectories([
                    BASE_DIR . 'vendor',
                    BASE_DIR . 'runtime' . DS . 'cache',
                    BASE_DIR . 'runtime' . DS . 'logs',
                    BASE_DIR . 'runtime' . DS . 'twig_cache',
                ]);

                clearTwigCache();
                json(true);
            } catch (Exception $e) {
                logException($e);
                json(false, $e->getMessage());
            }
        } break;

		case 'composer': {
			try {
				set_time_limit(0);
				composerInstall();
                json(true);
			} catch (Exception $e) {
				logException($e);
                json(false, $e->getMessage());
			}
		} break;

		case 'config': {
			try {
				checkDbConnection($_POST['db']);

				require BASE_DIR . 'vendor' . DS . 'autoload.php';
                $provider = new \GameX\Core\Configuration\Providers\JsonProvider();
				$config = new \GameX\Core\Configuration\Config($provider);
				$db = $config->getNode('db');
				$db->set('host', $_POST['db']['host']);
				$db->set('port', (int) $_POST['db']['port']);
				$db->set('username', $_POST['db']['user']);
				$db->set('password', $_POST['db']['pass']);
				$db->set('database', $_POST['db']['name']);
				$db->set('prefix', $_POST['db']['prefix']);

                $provider->setPath(BASE_DIR . 'config.json');
				$config->save();
                json(true);
			} catch (Exception $e) {
                logException($e);
                json(false, $e->getMessage());
			}
		} break;

		case 'migrations': {
			try {
				$container = getContainer(true);
				runMigrations($container);
                json(true);
			} catch (Exception $e) {
                logException($e);
                json(false, $e->getMessage());
			}
		}

		case 'admin': {
			try {
                $container = getContainer(true);
                /** @var \Illuminate\Database\Capsule\Manager $db */
                $db = $container['db'];
				$db->getConnection()->statement("SET foreign_key_checks=0");
				\GameX\Core\Auth\Models\UserModel::truncate();
				\GameX\Core\Auth\Models\PersistenceModel::truncate();
				$db->getConnection()->statement("SET foreign_key_checks=1");
				createUser($container, $_POST['login'], $_POST['email'], $_POST['pass']);
                json(true);
			} catch (Exception $e) {
                logException($e);
                json(false, $e->getMessage());
			}
		}

		default: {
            json(false, 'Unknown step ' . $step);
		}
	}
}
