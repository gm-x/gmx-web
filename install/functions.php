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

function composerInstall() {
	$tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('GameX', true) . DIRECTORY_SEPARATOR;
	$baseDir = dirname(__DIR__);
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

	dirname($baseDir);
	putenv('COMPOSER_HOME=' . $tempDir . '/vendor/bin/composer');

	$input = new \Symfony\Component\Console\Input\ArrayInput(['command' => 'install']);
	$output = new \Symfony\Component\Console\Output\NullOutput();
	$application = new \Composer\Console\Application();
	$application->setAutoExit(true);
	$application->run($input, $output);
}
