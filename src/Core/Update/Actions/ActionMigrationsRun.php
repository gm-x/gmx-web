<?php
namespace GameX\Core\Update\Actions;

use \GameX\Core\Update\ActionInterface;
use \Symfony\Component\Console\Output\NullOutput;
use \Phpmig\Api\PhpmigApplication;
use \GameX\Core\Container;

class ActionMigrationsRun implements ActionInterface {

    protected $dir;

    public function __construct($dir) {
        $this->dir = $dir;
    }

    public function run() {
        $container = new Container();
        $container['root'] = $this->dir . DIRECTORY_SEPARATOR;
        require $this->dir . DIRECTORY_SEPARATOR . 'phpmig.php';

        $output = new NullOutput();
        $app = new PhpmigApplication($container, $output);

        $app->up();
        return true;
    }
}