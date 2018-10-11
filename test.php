<?php
$skip = [
    '.idea',
    '.git',
    'runtime',
    'vendor',
    'upload',
    'config.json',
    'router.php',
    'composer.json',
    'composer.lock',
    'public/assets',
    'test.php',
    'manifest.json'
];

function checkToSkip($path) {
    global $skip;
    foreach ($skip as $item) {
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
$migrations = [];

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
    'version' => '0.0.1',
    'files' => $files,
    'migration' => $migrations,
], JSON_PRETTY_PRINT));


try {
    $old = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'manifest.json'), true);
    $new = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'manifest_new.json'), true);

    $actions = [];
    foreach ($new['files'] as $key => $value) {
        $path = __DIR__ . DIRECTORY_SEPARATOR . $key;
        if (!array_key_exists($key, $old['files'])) {
            $actions[] = [
                'action' => 'copy',
                'src' => $key,
                'dst' => $path
            ];
        } elseif ($value !== $old['files'][$key]) {
            if (!is_readable($path)) {
                $actions[] = [
                    'action' => 'copy',
                    'src' => $key,
                    'dst' => $path
                ];
            } elseif ($old['files'][$key] !== sha1_file($path)) {
                throw new Exception('File ' . $key . ' is modified');
            } elseif (!is_writable($path)) {
                throw new Exception('Haven\'t permssions to write file ' . $key);
            } else {
                $actions[] = [
                    'action' => 'replace',
                    'src' => $key,
                    'dst' => $path
                ];
            }
        }
    }

    foreach ($old['files'] as $key => $value) {
        if (array_key_exists($key, $new['files'])) {
            continue;
        }

        if (!is_readable($path)) {
            continue;
        }

        $path = __DIR__ . DIRECTORY_SEPARATOR . $key;
        if ($value !== sha1_file($path)) {
            throw new Exception('File ' . $key . ' is modified');
        }

        $actions[] = [
            'action' => 'delete',
            'src' => $key,
            'dst' => $path
        ];
    }
} catch (Exception $e) {
    var_dump($e);
}