<?php
if (php_sapi_name() !== 'cli-server') {
    die('this is only for the php development server');
}

if (is_file($_SERVER['DOCUMENT_ROOT'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    // probably a static file...
    return false;
}

if (preg_match('#^/install#', $_SERVER['REQUEST_URI'])) {
	require __DIR__ . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'index.php';
} else if (preg_match('#^/cron#', $_SERVER['REQUEST_URI'])) {
	require __DIR__ . DIRECTORY_SEPARATOR . 'cron.php';
} else {
	require __DIR__ . DIRECTORY_SEPARATOR . 'index.php';
}
