<?php
namespace GameX\Core\Update;

use \GameX\Core\Update\Actions\ActionCopyFile;
use \GameX\Core\Update\Actions\ActionDeleteFile;
use \GameX\Core\Update\Actions\ActionComposerInstall;
use \GameX\Core\Update\Actions\ActionMigrationsRun;
use \GameX\Core\Update\Actions\ActionClearCache;

class Updater {
    protected $baseDir;
    protected $updateDir;

    public function __construct($baseDir, $updateDir) {
        $this->baseDir = $baseDir;
        $this->updateDir = $updateDir;
    }

    public function run(Manifest $old, Manifest $new) {
        $actions = new Actions();

//        $oldFiles = $old->getFiles();
//        $newFiles = $new->getFiles();
//
//        foreach ($newFiles as $key => $value) {
//            $source = $this->updateDir . DIRECTORY_SEPARATOR . $key;
//            $destination = $this->baseDir . DIRECTORY_SEPARATOR . $key;
//            if (!array_key_exists($key, $oldFiles)) {
//                $actions->add(new ActionCopyFile($source, $destination));
//            } elseif ($value !== $oldFiles[$key]) {
//                if (!is_readable($destination)) {
//                    $actions->add(new ActionCopyFile($source, $destination));
//                } elseif ($old['files'][$key] !== sha1_file($destination)) {
//                    throw new \Exception('File ' . $key . ' is modified');
//                } elseif (!is_writable($destination)) {
//                    throw new \Exception('Haven\'t permssions to write file ' . $key);
//                } else {
//                    $actions->add(new ActionCopyFile($source, $destination));
//                }
//            }
//        }
//
//        foreach ($oldFiles as $key => $value) {
//            if (array_key_exists($key, $newFiles)) {
//                continue;
//            }
//
//            $destination = $this->baseDir . DIRECTORY_SEPARATOR . $key;
//            if (!is_readable($destination)) {
//                continue;
//            }
//
//            if ($value !== sha1_file($destination)) {
//                throw new \Exception('File ' . $key . ' is modified');
//            }
//
//            $actions->add(new ActionDeleteFile($destination));
//        }

        $actions->add(new ActionComposerInstall($this->baseDir));
        $actions->add(new ActionMigrationsRun($this->baseDir));
        $actions->add(new ActionClearCache($this->baseDir . 'runtime' . DIRECTORY_SEPARATOR . 'cache'));
        $actions->add(new ActionClearCache($this->baseDir . 'runtime' . DIRECTORY_SEPARATOR . 'twig_cache'));

        $actions->run();
    }
}