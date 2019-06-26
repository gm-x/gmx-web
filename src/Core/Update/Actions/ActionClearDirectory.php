<?php
namespace GameX\Core\Update\Actions;

use \GameX\Core\Update\ActionInterface;
use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;

class ActionClearDirectory implements ActionInterface {

    /**
     * @var string
     */
    protected $dir;

    /**
     * @param string $dir
     */
    public function __construct($dir) {
        $this->dir = $dir;
    }

    /**
     * @inheritdoc
     */
    public function run() {
    	if (!is_dir(($this->dir))){
    		return true;
	    }

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
