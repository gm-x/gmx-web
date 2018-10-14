<?php
if (php_sapi_name() !== 'cli') {
    die('This is only for the cli mode');
}

if ($argc < 2) {
    die('Version is not provided');
}

define('ROOT', __DIR__ . DIRECTORY_SEPARATOR);

$skipDirectories = [
    '.idea',
    '.git',
    'runtime',
    'vendor',
    'upload',
    'config.json',
    'router.php',
    'manifest.json',
    'manifest.php',
];

function checkToSkip($path) {
    global $skipDirectories;
    foreach ($skipDirectories as $item) {
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
$iterator = new RecursiveDirectoryIterator(ROOT, $flags);
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

$zip->close();

echo 'Completed successfully' . PHP_EOL;
