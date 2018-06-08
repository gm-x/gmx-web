<?php
include __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';

try {
	composerInstall();
	echo 'Done';
} catch (Exception $e) {
	echo 'Error ' . (string)$e;
}
