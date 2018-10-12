<?php
namespace GameX\Core\Update\Actions;

use \GameX\Core\Update\ActionInterface;
use \Symfony\Component\Console\Input\ArrayInput;
use \Symfony\Component\Console\Output\NullOutput;
use \Composer\Console\Application;

class ActionComposerInstall implements ActionInterface {

    protected $dir;

    public function __construct($dir) {
        $this->dir = $dir;
    }

    public function run() {
        chdir($this->dir);
        putenv('COMPOSER_HOME=' . $this->dir . 'vendor/bin/composer');
        putenv('COMPOSER_VENDOR_DIR=' . $this->dir . 'vendor');
        putenv('COMPOSER_BIN_DIR=' . $this->dir . 'vendor/bin');

        $input = new ArrayInput(['command' => 'install']);
        $output = new NullOutput();
        $application = new Application();
        $application->setAutoExit(false);
        $application->run($input, $output);
        return true;
    }
}