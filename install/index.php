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

