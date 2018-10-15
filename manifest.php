<?php
if (php_sapi_name() !== 'cli') {
    die('This is only for the cli mode');
}

if ($argc < 2) {
    die('Version is not provided');
}

define('ROOT', __DIR__ . DIRECTORY_SEPARATOR);
define('INSTALL', ROOT . 'public' . DIRECTORY_SEPARATOR . 'install'. DIRECTORY_SEPARATOR);

$ignoreList = [
    '^\.idea',
    '^\.git',
    '^\.gitignore',
    '\.gitkeep$',
    '^runtime',
    '^vendor',
    '^upload',
    '^config\.json',
    '^router\.php',
    '^manifest\.json',
    '^manifest\.php',
    '^updates_[^.]+\.zip',
    '^public/install',
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

$zip = new ZipArchive();
$filename = ROOT . 'updates_' . $argv[1] . '.zip';

if (!$zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
    die('Error while creating file ' . $filename);
}

foreach ($files as $path => $v) {
    $zip->addFile(ROOT . $path, $path);
}

$zip->addFile(ROOT . 'manifest.json', 'manifest.json');

$flags = FilesystemIterator::KEY_AS_PATHNAME
    | FilesystemIterator::CURRENT_AS_SELF
    | FilesystemIterator::UNIX_PATHS
    | FilesystemIterator::SKIP_DOTS;
$iterator = new RecursiveDirectoryIterator(INSTALL, $flags);
$iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);

/**
 * @var string $path
 * @var RecursiveDirectoryIterator $it
 */
foreach($iterator as $path => $it) {
    if ($it->isFile()) {
        $zip->addFile(INSTALL . $it->getSubPathName(), 'public/install/' . $it->getSubPathName());
    }
}

$zip->close();

echo 'Completed successfully' . PHP_EOL;
