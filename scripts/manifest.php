<?php
if (php_sapi_name() !== 'cli') {
    die('This is only for the cli mode');
}

if ($argc < 2) {
    die('Version is not provided');
}

define('ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);

$ignoreList = [
    '^\.idea',
    '^\.git',
    '\.gitignore',
    '\.gitkeep$',
    '^runtime',
    '^vendor',
    '^uploads',
    '^\.travis\.yml',
    '^config\.json',
    '^config\.php',
    '^router\.php',
    '^manifest\.json',
    '^manifest\.php',
    '^updates_[^.]+\.zip',
    '^install',
    '\.less$'
];

function isIgnored($path) {
    global $ignoreList;
    foreach ($ignoreList as $item) {
        $pattern = '#' . $item . '#';
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
$iterator = new RecursiveDirectoryIterator(ROOT, $flags);
$iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
$files = [];

/**
 * @var string $path
 * @var RecursiveDirectoryIterator $it
 */
foreach($iterator as $path => $it) {
    if (!isIgnored($iterator->getSubPathName()) && $it->isFile()) {
        $files[$it->getSubPathName()] = sha1_file($path);
    }
}

file_put_contents(ROOT . 'manifest.json', json_encode([
    'version' => $argv[1],
    'files' => $files
], JSON_PRETTY_PRINT));

