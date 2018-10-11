<?php
namespace GameX\Core\Update\Actions;

use \GameX\Core\Update\Action;

class ActionCopy extends Action {

    public function run() {
        $dir = dirname($this->destination);
        if (!is_dir($dir)) {
            mkdir($dir, 644, true);
        }
        return copy($this->source, $this->destination);
    }
}