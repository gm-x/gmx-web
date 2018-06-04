<?php
namespace GameX\Core\Forms\Elements;

class FormInputEmail extends FormInput {

    /**
     * @inheritdoc
     */
    public function getType() {
        return 'email';
    }
}
