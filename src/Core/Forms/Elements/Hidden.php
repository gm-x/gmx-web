<?php
namespace GameX\Core\Forms\Elements;

class Hidden extends Input {

    /**
     * @inheritdoc
     */
    public function getType() {
        return 'hidden';
    }
}
