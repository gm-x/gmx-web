<?php
namespace GameX\Core\Update;

use \GameX\Core\Update\Actions\ActionCopyFile;
use \GameX\Core\Update\Actions\ActionDeleteFile;
use \GameX\Core\Update\Actions\ActionComposerInstall;
use \GameX\Core\Update\Actions\ActionMigrationsRun;
use \GameX\Core\Update\Actions\ActionClearDirectory;
use \GameX\Core\Update\Exceptions\LastVersionException;
use \GameX\Core\Update\Exceptions\IsModifiedException;
use \GameX\Core\Update\Exceptions\FileNotExistsException;
use \GameX\Core\Update\Exceptions\CanWriteException;

class Updater {
    
    /**
     * @var Manifest
     */
    protected $manifest;

    /**
     * @param Manifest $manifest
     */
    public function __construct(Manifest $manifest) {
        $this->manifest = $manifest;
    }

    /**
     * @param Manifest $updates
     * @throws \Exception
     */
    public function run(Manifest $updates) {
        if (version_compare($this->manifest->getVersion(), $updates->getVersion(), '>=')) {
            throw new LastVersionException();
        }
        
        $actions = new Actions();

        $baseFiles = $this->manifest->getFiles();
        $updatesFiles = $updates->getFiles();
        $baseDir = $this->manifest->getDir();
        $updatesDir = $updates->getDir();

        foreach ($updatesFiles as $key => $value) {
            $source = $updatesDir . $key;
            $destination = $baseDir . $key;
            if (!array_key_exists($key, $baseFiles)) {
                $actions->add(new ActionCopyFile($source, $destination));
            } elseif ($value !== $baseFiles[$key]) {
                if (!is_readable($source)) {
                    throw new FileNotExistsException($source);
                } else if (!is_readable($destination)) {
                    $actions->add(new ActionCopyFile($source, $destination));
                } elseif ($baseFiles[$key] !== sha1_file($destination)) {
                    throw new IsModifiedException($key);
                } elseif (!is_writable($destination)) {
                    throw new CanWriteException($destination);
                } else {
                    $actions->add(new ActionCopyFile($source, $destination));
                }
            }
        }

        foreach ($baseFiles as $key => $value) {
            if (array_key_exists($key, $updatesFiles)) {
                continue;
            }

            $destination = $baseDir . $key;
            if (!is_file($destination)) {
                continue;
            }

            if ($value !== sha1_file($destination)) {
                throw new IsModifiedException($key);
            }

            $actions->add(new ActionDeleteFile($destination));
        }

        $actions->add(new ActionComposerInstall($baseDir));
        $actions->add(new ActionMigrationsRun($baseDir));
        $actions->add(new ActionClearDirectory($baseDir . 'runtime' . DIRECTORY_SEPARATOR . 'cache'));
        $actions->add(new ActionClearDirectory($baseDir . 'runtime' . DIRECTORY_SEPARATOR . 'twig_cache'));
        $actions->add(new ActionCopyFile($updatesDir . 'manifest.json', $baseDir . 'manifest.json'));
        $actions->add(new ActionClearDirectory($updatesDir));
    
        $actions->run();
    }
}
