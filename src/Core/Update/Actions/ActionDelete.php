<?php
namespace GameX\Core\Update\Actions;

use \GameX\Core\Update\Action;

class ActionDelete extends Action {

    public function run() {
        return unlink($this->destination);
    }
}