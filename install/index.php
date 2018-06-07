<?php
include __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	echo render('template', [
		'baseUrl' => getBaseUrl()
	]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$step = isset($_GET['step']) ? (int)$_GET['step'] : null;
	switch ($step) {
		case 1: {
			try {
				composerInstall();
				json([
					'status' => $res
				]);
			} catch (Exception $e) {
				json([
					'status' => false,
					'message' => $e->getMessage()
				]);
			}
		} break;
	}
}



/*

STEPS
1. Check compability
2. Install composer
3. Download dependencies
4. Make config.json file
5. Run migrations
6. Create default roles admins/users
7. Create admin user and assign to role

 */
