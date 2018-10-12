<?php
if (php_sapi_name() !== 'cli') {
    die('This is only for the cli mode');
}

if ($argc < 2) {
    die('Version is not provided');
}

define('SKIP_DIRECTORIES', [
    '.idea',
    '.git',
    'runtime',
    'vendor',
    'upload',
    'config.json',
    'router.php',
    'manifest.json'
]);

function checkToSkip($path) {
    foreach (SKIP_DIRECTORIES as $item) {
        $pattern = '#^' . preg_quote($item, '#') . '#';
        if (preg_match($pattern, $path)) {
            return true;
        }
    }

    return false;
}

$flags = FilesystemIterator::KEY_AS_PATHNAME
    | FilesystemIterator::CURRENT_AS_SELF
    | FilesystemIterator::UNIX_PATHS
    | FilesystemIterator::SKIP_DOTS;
$iterator = new RecursiveDirectoryIterator(__DIR__, $flags);
$iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
$files = [];

/**
 * @var string $path
 * @var RecursiveDirectoryIterator $it
 */
foreach($iterator as $path => $it) {
    if (!checkToSkip($iterator->getSubPathName()) && $it->isFile()) {
        $files[$it->getSubPathName()] = sha1_file($path);
    }

    if ($it->isFile() && $it->getSubPath() === 'migrations') {
        $migrations[] = $it->getFilename();
    }
}

file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'manifest.json', json_encode([
    'version' => $argv[1],
    'files' => $files
], JSON_PRETTY_PRINT));

echo 'Completed successfully';