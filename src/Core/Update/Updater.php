<?php
namespace GameX\Core\Update;

use \GameX\Core\Update\Actions\ActionCopy;
use \GameX\Core\Update\Actions\ActionDelete;

class Updater {
    protected $baseDir;
    protected $updateDir;

    public function __construct($baseDir, $updateDir) {
        $this->baseDir = $baseDir;
        $this->updateDir = $updateDir;
    }

    public function calculateActions(Manifest $old, Manifest $new) {
        $oldFiles = $old->getFiles();
        $newFiles = $new->getFiles();

        $actions = new Actions();
        foreach ($newFiles as $key => $value) {
            $source = $this->updateDir . DIRECTORY_SEPARATOR . $key;
            $destination = $this->baseDir . DIRECTORY_SEPARATOR . $key;
            if (!array_key_exists($key, $oldFiles)) {
                $actions->add(new ActionCopy($source, $destination));
            } elseif ($value !== $oldFiles[$key]) {
                if (!is_readable($destination)) {
                    $actions->add(new ActionCopy($source, $destination));
                } elseif ($old['files'][$key] !== sha1_file($destination)) {
                    throw new \Exception('File ' . $key . ' is modified');
                } elseif (!is_writable($destination)) {
                    throw new \Exception('Haven\'t permssions to write file ' . $key);
                } else {
                    $actions->add(new ActionCopy($source, $destination));
                }
            }
        }

        foreach ($oldFiles as $key => $value) {
            if (array_key_exists($key, $newFiles)) {
                continue;
            }

            $destination = $this->baseDir . DIRECTORY_SEPARATOR . $key;
            if (!is_readable($destination)) {
                continue;
            }

            if ($value !== sha1_file($destination)) {
                throw new \Exception('File ' . $key . ' is modified');
            }

            $actions->add(new ActionDelete(null, $destination));
        }
        return $actions;
    }

    public function runComposer() {
        chdir($this->baseDir);
        //	https://getcomposer.org/doc/03-cli.md#composer-vendor-dir
        putenv('COMPOSER_HOME=' . $this->baseDir . 'vendor/bin/composer');
        putenv('COMPOSER_VENDOR_DIR=' . $this->baseDir . 'vendor');
        putenv('COMPOSER_BIN_DIR=' . $this->baseDir . 'vendor/bin');

        $input = new \Symfony\Component\Console\Input\ArrayInput(['command' => 'install']);
        $output = new \Symfony\Component\Console\Output\NullOutput();
        $application = new \Composer\Console\Application();
        $application->setAutoExit(false);
        $application->run($input, $output);
    }
}