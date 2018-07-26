<?php
namespace GameX\Core\Forms\Elements;

class Email extends Input {

    /**
     * @inheritdoc
     */
    public function getType() {
        return 'email';
    }
}
