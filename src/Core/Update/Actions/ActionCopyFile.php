<?php
namespace GameX\Core\Update\Actions;

use \GameX\Core\Update\ActionInterface;

class ActionCopyFile implements ActionInterface {

    protected $source;
    protected $destination;

    public function __construct($source, $destination) {
        $this->source = $source;
        $this->destination = $destination;
    }

    public function run() {
        if (!is_file($this->source)) {
            return false;
        }

        $dir = dirname($this->destination);
        if (!is_dir($dir)) {
            mkdir($dir, 644, true);
        }
        return copy($this->source, $this->destination);
    }
}