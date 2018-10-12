<?php
namespace GameX\Core\Update\Actions;

use \GameX\Core\Update\ActionInterface;

class ActionDeleteFile implements ActionInterface {

    protected $destination;

    public function __construct($destination) {
        $this->destination = $destination;
    }

    public function run() {
        if (!is_file($this->destination)) {
            return false;
        }
        return unlink($this->destination);
    }
}