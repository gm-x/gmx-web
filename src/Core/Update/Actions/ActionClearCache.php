<?php
namespace GameX\Core\Update\Actions;

use \GameX\Core\Update\ActionInterface;
use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;

class ActionClearCache implements ActionInterface {

    protected $dir;

    public function __construct($dir) {
        $this->dir = $dir;
    }

    public function run() {
        var_dump($this->dir);
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var \SplFileInfo $info */
        foreach ($files as $info) {
            if ($info->isDir()) {
                rmdir($info->getRealPath());
            } else {
                unlink($info->getRealPath());
            }
        }
        return true;
    }
}